<?php

require_once __DIR__ . "/../Repositorios/PedidoRepository.php";
require_once __DIR__ . "/../Repositorios/ProductoRepository.php";
require_once __DIR__ . "/../Repositorios/ComercianteLocalRepository.php";
require_once __DIR__ . "/../Modelos/Pedido.php";
require_once __DIR__ . "/../Modelos/DetallePedido.php";
require_once __DIR__ . "/ClienteController.php";
require_once __DIR__ . "/ComercianteController.php";
require_once __DIR__ . "/LocalController.php";
require_once __DIR__ . "/../Comun/Sesion.php";

class PedidoController
{
    private const CANTIDAD_MAXIMA_POR_PRODUCTO = 99;
    private const RETIRO_CODIGO_LARGO = 6;
    private const RETIRO_CODIGO_CARACTERES = "ABCDEFGHJKMNPQRSTUVWXYZ23456789";

    private PedidoRepository $pedidoRepository;
    private ProductoRepository $productoRepository;
    private ComercianteLocalRepository $comercianteLocalRepository;
    private LocalController $localController;

    public function __construct()
    {
        $this->pedidoRepository = new PedidoRepository();
        $this->productoRepository = new ProductoRepository();
        $this->comercianteLocalRepository = new ComercianteLocalRepository();
        $this->localController = new LocalController();
    }

    public function crearPedido(array $usuarioSesion, int $idLocal, array $items): array
    {
        $cliente = $this->resolverCliente($usuarioSesion);

        $local = $this->localController->buscar($idLocal);
        if ($local === null || !$local->isActivo()) {
            throw new InvalidArgumentException("Este local no está disponible para recibir pedidos en este momento");
        }

        if ($usuarioSesion["tipo"] === Sesion::TIPO_COMERCIANTE
            && $this->localController->perteneceAComerciante($idLocal, $usuarioSesion["id"])) {
            throw new InvalidArgumentException("No puedes comprar en tu propio local");
        }

        $cantidades = $this->normalizarItems($items);

        $pedido = new Pedido($cliente->getIdCliente(), $idLocal);

        foreach ($cantidades as $idProducto => $cantidad) {
            $producto = $this->productoRepository->obtenerPorId($idProducto);

            if ($producto === null || !$producto->isActivo()) {
                throw new InvalidArgumentException("Uno de los productos ya no está disponible");
            }

            if ($producto->isVencido()) {
                throw new InvalidArgumentException("La oferta de \"{$producto->getNombre()}\" ya terminó");
            }

            if (!$this->pedidoRepository->productoSeOfreceEnLocal($idProducto, $idLocal)) {
                throw new InvalidArgumentException("\"{$producto->getNombre()}\" no se vende en este local");
            }

            if ($producto->getCantidadDisponible() < $cantidad) {
                throw new InvalidArgumentException(
                    $producto->isAgotado()
                        ? "\"{$producto->getNombre()}\" está agotado"
                        : "Solo quedan {$producto->getCantidadDisponible()} unidades de \"{$producto->getNombre()}\""
                );
            }

            $pedido->agregarDetalle(new DetallePedido(
                $idProducto,
                $producto->getNombre(),
                $cantidad,
                $producto->getPrecioOriginal(),
                $producto->getPorcentajeDescuento(),
                $producto->getPrecioFinal()
            ));
        }

        $idPedido = $this->pedidoRepository->insertar($pedido, $cliente->getIdUsuario(), Sesion::TIPO_CLIENTE);

        return ["idPedido" => $idPedido, "total" => $pedido->getTotal()];
    }

    public function listarPedidosDeCliente(array $usuarioSesion): array
    {
        $cliente = $this->resolverCliente($usuarioSesion);
        return $this->pedidoRepository->obtenerPorCliente($cliente->getIdCliente());
    }

    public function cancelarPedido(array $usuarioSesion, int $idPedido, ?string $motivo = null): bool
    {
        $cliente = $this->resolverCliente($usuarioSesion);
        $pedido = $this->obtenerPedidoOFallar($idPedido);

        if ($pedido->getIdCliente() !== $cliente->getIdCliente()) {
            throw new InvalidArgumentException("Este pedido no es tuyo");
        }

        $this->validarTransicion($pedido, Pedido::ESTADO_CANCELADO, "cancelar");

        $motivo = $this->limpiarMotivo($motivo) ?? "Cancelado por el cliente";

        return $this->pedidoRepository->cambiarEstado(
            $pedido,
            Pedido::ESTADO_CANCELADO,
            null,
            $motivo,
            $cliente->getIdUsuario(),
            Sesion::TIPO_CLIENTE
        );
    }

    public function listarPedidosRecibidos(int $idComerciante, ?string $estado = null): array
    {
        if ($estado !== null && !in_array($estado, Pedido::ESTADOS, true)) {
            throw new InvalidArgumentException("Estado de pedido no válido");
        }

        $idsLocales = $this->comercianteLocalRepository->obtenerLocalesPorComerciante($idComerciante);
        return $this->pedidoRepository->obtenerPorLocales($idsLocales, $estado);
    }

    public function confirmarPedido(int $idComerciante, int $idPedido): string
    {
        $pedido = $this->obtenerPedidoDeComerciante($idComerciante, $idPedido);
        $this->validarTransicion($pedido, Pedido::ESTADO_CONFIRMADO, "confirmar");

        $retiroCodigo = $this->generarRetiroCodigo($pedido->getIdLocal());

        $this->pedidoRepository->cambiarEstado(
            $pedido,
            Pedido::ESTADO_CONFIRMADO,
            $retiroCodigo,
            null,
            $this->idUsuarioDeComerciante($idComerciante),
            Sesion::TIPO_COMERCIANTE
        );

        return $retiroCodigo;
    }

    public function rechazarPedido(int $idComerciante, int $idPedido, ?string $motivo): bool
    {
        $pedido = $this->obtenerPedidoDeComerciante($idComerciante, $idPedido);
        $this->validarTransicion($pedido, Pedido::ESTADO_RECHAZADO, "rechazar");

        $motivo = $this->limpiarMotivo($motivo);
        if ($motivo === null) {
            throw new InvalidArgumentException("Indica el motivo del rechazo para que el cliente sepa qué pasó");
        }

        return $this->pedidoRepository->cambiarEstado(
            $pedido,
            Pedido::ESTADO_RECHAZADO,
            null,
            $motivo,
            $this->idUsuarioDeComerciante($idComerciante),
            Sesion::TIPO_COMERCIANTE
        );
    }

    public function confirmarEntrega(int $idComerciante, int $idPedido, string $retiroCodigoIngresado): bool
    {
        $pedido = $this->obtenerPedidoDeComerciante($idComerciante, $idPedido);
        $this->validarTransicion($pedido, Pedido::ESTADO_ENTREGADO, "marcar como entregado");

        $retiroCodigoIngresado = strtoupper(preg_replace('/\s+/', '', $retiroCodigoIngresado));

        if ($retiroCodigoIngresado === ""
            || !hash_equals((string) $pedido->getRetiroCodigo(), $retiroCodigoIngresado)) {
            throw new InvalidArgumentException("El código de retiro no coincide. Pídele al cliente que lo revise.");
        }

        return $this->pedidoRepository->cambiarEstado(
            $pedido,
            Pedido::ESTADO_ENTREGADO,
            null,
            null,
            $this->idUsuarioDeComerciante($idComerciante),
            Sesion::TIPO_COMERCIANTE
        );
    }

    public function obtenerParaComprobante(array $usuarioSesion, int $idPedido): array
    {
        $datos = $this->pedidoRepository->obtenerPorId($idPedido);
        if ($datos === null) {
            throw new InvalidArgumentException("El pedido no existe");
        }

        $pedido = $datos["pedido"];

        if (!$this->puedeVerPedido($usuarioSesion, $pedido)) {
            throw new InvalidArgumentException("No tienes permiso para ver este pedido");
        }

        $datos["historial"] = $this->pedidoRepository->obtenerHistorialEstado($idPedido);
        return $datos;
    }

    public function esDuenoDelLocal(array $usuarioSesion, Pedido $pedido): bool
    {
        return $usuarioSesion["tipo"] === Sesion::TIPO_COMERCIANTE
            && $this->localController->perteneceAComerciante($pedido->getIdLocal(), $usuarioSesion["id"]);
    }

    public function resolverCliente(array $usuarioSesion): Cliente
    {
        if ($usuarioSesion["tipo"] === Sesion::TIPO_CLIENTE) {
            $cliente = (new ClienteController())->buscar($usuarioSesion["id"]);
            if ($cliente === null) {
                throw new InvalidArgumentException("No se pudo identificar tu cuenta");
            }
            return $cliente;
        }

        if ($usuarioSesion["tipo"] === Sesion::TIPO_COMERCIANTE) {
            $comerciante = (new ComercianteController())->buscar($usuarioSesion["id"]);
            if ($comerciante === null) {
                throw new InvalidArgumentException("No se pudo identificar tu cuenta");
            }

            $cliente = (new ClienteController())->buscarPorIdUsuario($comerciante->getIdUsuario());
            if ($cliente === null) {
                throw new InvalidArgumentException("No se pudo identificar tu perfil de cliente");
            }
            return $cliente;
        }

        throw new InvalidArgumentException("El administrador no puede realizar compras");
    }

    private function puedeVerPedido(array $usuarioSesion, Pedido $pedido): bool
    {
        if ($usuarioSesion["tipo"] === Sesion::TIPO_SUPERADMIN) {
            return true;
        }

        if ($this->esDuenoDelLocal($usuarioSesion, $pedido)) {
            return true;
        }

        try {
            $cliente = $this->resolverCliente($usuarioSesion);
            return $cliente->getIdCliente() === $pedido->getIdCliente();
        } catch (InvalidArgumentException $e) {
            return false;
        }
    }

    private function obtenerPedidoOFallar(int $idPedido): Pedido
    {
        $datos = $this->pedidoRepository->obtenerPorId($idPedido);
        if ($datos === null) {
            throw new InvalidArgumentException("El pedido no existe");
        }
        return $datos["pedido"];
    }

    private function obtenerPedidoDeComerciante(int $idComerciante, int $idPedido): Pedido
    {
        $pedido = $this->obtenerPedidoOFallar($idPedido);

        if (!$this->localController->perteneceAComerciante($pedido->getIdLocal(), $idComerciante)) {
            throw new InvalidArgumentException("Este pedido no pertenece a ninguno de tus locales");
        }

        return $pedido;
    }

    private function validarTransicion(Pedido $pedido, string $nuevoEstado, string $accion): void
    {
        if (!$pedido->puedeCambiarA($nuevoEstado)) {
            throw new InvalidArgumentException(
                "No se puede {$accion} un pedido que está en estado \"{$pedido->getEstado()}\""
            );
        }
    }

    private function normalizarItems(array $items): array
    {
        if (empty($items)) {
            throw new InvalidArgumentException("Selecciona al menos un producto");
        }

        $cantidades = [];

        foreach ($items as $item) {
            $idProducto = (int) ($item["idProducto"] ?? 0);
            $cantidad = filter_var($item["cantidad"] ?? null, FILTER_VALIDATE_INT);

            if ($idProducto <= 0) {
                throw new InvalidArgumentException("Producto no válido");
            }

            if ($cantidad === false || $cantidad < 1) {
                throw new InvalidArgumentException("La cantidad debe ser un número entero mayor a 0");
            }

            $cantidades[$idProducto] = ($cantidades[$idProducto] ?? 0) + $cantidad;

            if ($cantidades[$idProducto] > self::CANTIDAD_MAXIMA_POR_PRODUCTO) {
                throw new InvalidArgumentException(
                    "Puedes pedir como máximo " . self::CANTIDAD_MAXIMA_POR_PRODUCTO . " unidades por producto"
                );
            }
        }

        return $cantidades;
    }

    private function generarRetiroCodigo(int $idLocal): string
    {
        $caracteres = self::RETIRO_CODIGO_CARACTERES;
        $maximo = strlen($caracteres) - 1;

        for ($intento = 0; $intento < 10; $intento++) {
            $retiroCodigo = "";
            for ($i = 0; $i < self::RETIRO_CODIGO_LARGO; $i++) {
                $retiroCodigo .= $caracteres[random_int(0, $maximo)];
            }

            if (!$this->pedidoRepository->existeRetiroCodigoActivoEnLocal($idLocal, $retiroCodigo)) {
                return $retiroCodigo;
            }
        }

        throw new RuntimeException("No se pudo generar un código de retiro, intenta de nuevo");
    }

    private function limpiarMotivo(?string $motivo): ?string
    {
        $motivo = trim((string) $motivo);

        if ($motivo === "") {
            return null;
        }

        return function_exists('mb_substr') ? mb_substr($motivo, 0, 255) : substr($motivo, 0, 255);
    }

    private function idUsuarioDeComerciante(int $idComerciante): ?int
    {
        $comerciante = (new ComercianteController())->buscar($idComerciante);
        return $comerciante?->getIdUsuario();
    }
}