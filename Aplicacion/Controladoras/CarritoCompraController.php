<?php

require_once __DIR__ . "/../Repositorios/CarritoCompraRepository.php";
require_once __DIR__ . "/../Repositorios/ProductoRepository.php";
require_once __DIR__ . "/../Repositorios/PedidoRepository.php";
require_once __DIR__ . "/../Modelos/CarritoCompra.php";
require_once __DIR__ . "/../Modelos/CarritoCompraDetalle.php";
require_once __DIR__ . "/PedidoController.php";
require_once __DIR__ . "/LocalController.php";
require_once __DIR__ . "/../Comun/Sesion.php";
require_once __DIR__ . "/../Modelos/PedidoPago.php";

class CarritoCompraController
{
    public const ESTADO_OK = "ok";
    public const ESTADO_INSUFICIENTE = "insuficiente";
    public const ESTADO_AGOTADO = "agotado";
    public const ESTADO_NO_DISPONIBLE = "no_disponible";

    private CarritoCompraRepository $carritoCompraRepository;
    private ProductoRepository $productoRepository;
    private PedidoRepository $pedidoRepository;
    private PedidoController $pedidoController;
    private LocalController $localController;

    public function __construct()
    {
        $this->carritoCompraRepository = new CarritoCompraRepository();
        $this->productoRepository = new ProductoRepository();
        $this->pedidoRepository = new PedidoRepository();
        $this->pedidoController = new PedidoController();
        $this->localController = new LocalController();
    }

    public function agregarProducto(array $usuarioSesion, int $idLocal, int $idProducto, int $cantidad): array
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $local = $this->validarLocalParaComprar($usuarioSesion, $idLocal);

        if ($cantidad < 1) {
            throw new InvalidArgumentException("La cantidad debe ser un número entero mayor a 0");
        }

        $producto = $this->validarProductoDelLocal($idProducto, $idLocal);

        $carritoCompraExistente = $this->carritoCompraRepository->buscarPorClienteYLocal($cliente->getIdCliente(), $idLocal);
        $yaEnCarritoCompra = $carritoCompraExistente?->cantidadDeProducto($idProducto) ?? 0;
        $cantidadNueva = $yaEnCarritoCompra + $cantidad;

        $this->validarStock($producto, $cantidadNueva, $yaEnCarritoCompra);

        $detalle = new CarritoCompraDetalle($idProducto, $cantidadNueva);

        $carritoCompra = $carritoCompraExistente
            ?? $this->carritoCompraRepository->obtenerOCrear($cliente->getIdCliente(), $idLocal);

        $this->carritoCompraRepository->guardarCantidad($carritoCompra, $idProducto, $detalle->getCantidad());

        return [
            "localNombre" => $local->getNombreLocal(),
            "productoNombre" => $producto->getNombre(),
            "cantidadEnCarrito" => $cantidadNueva,
            "totalProductos" => $this->carritoCompraRepository->contarProductosDeCliente($cliente->getIdCliente())
        ];
    }

    public function cambiarCantidad(array $usuarioSesion, int $idLocal, int $idProducto, int $cantidad): void
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $carritoCompra = $this->obtenerCarritoCompraOFallar($cliente->getIdCliente(), $idLocal);

        if ($carritoCompra->obtenerDetalle($idProducto) === null) {
            throw new InvalidArgumentException("Ese producto no está en tu carrito");
        }

        $detalle = new CarritoCompraDetalle($idProducto, $cantidad);

        $producto = $this->validarProductoDelLocal($idProducto, $idLocal);
        $this->validarStock($producto, $detalle->getCantidad());

        $this->carritoCompraRepository->guardarCantidad($carritoCompra, $idProducto, $detalle->getCantidad());
    }

    public function quitarProducto(array $usuarioSesion, int $idLocal, int $idProducto): int
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $carritoCompra = $this->obtenerCarritoCompraOFallar($cliente->getIdCliente(), $idLocal);

        $this->carritoCompraRepository->quitarProducto($carritoCompra->getIdCarritoCompra(), $idProducto);

        $carritoCompra = $this->carritoCompraRepository->buscarPorClienteYLocal($cliente->getIdCliente(), $idLocal);
        if ($carritoCompra !== null && $carritoCompra->estaVacio()) {
            $this->carritoCompraRepository->eliminar($carritoCompra->getIdCarritoCompra());
        }

        return $this->carritoCompraRepository->contarProductosDeCliente($cliente->getIdCliente());
    }

    public function vaciar(array $usuarioSesion, int $idLocal): int
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $carritoCompra = $this->obtenerCarritoCompraOFallar($cliente->getIdCliente(), $idLocal);

        $this->carritoCompraRepository->eliminar($carritoCompra->getIdCarritoCompra());

        return $this->carritoCompraRepository->contarProductosDeCliente($cliente->getIdCliente());
    }

    public function confirmar(array $usuarioSesion, int $idLocal, PedidoPago $pago): array
        {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $carritoCompra = $this->obtenerCarritoCompraOFallar($cliente->getIdCliente(), $idLocal);

        if ($carritoCompra->estaVacio()) {
            throw new InvalidArgumentException("Tu carrito está vacío");
        }

        $resultado = $this->pedidoController->crearPedido($usuarioSesion, $idLocal, $carritoCompra->aItemsDePedido(), $pago);

        $this->carritoCompraRepository->eliminar($carritoCompra->getIdCarritoCompra());

        $resultado["totalProductos"] = $this->carritoCompraRepository->contarProductosDeCliente($cliente->getIdCliente());
        return $resultado;
    }

    public function contarProductos(array $usuarioSesion): int
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        return $this->carritoCompraRepository->contarProductosDeCliente($cliente->getIdCliente());
    }

    public function listar(array $usuarioSesion): array
    {
        $cliente = $this->pedidoController->resolverCliente($usuarioSesion);
        $carritosCompra = array_filter(
            $this->carritoCompraRepository->listarPorCliente($cliente->getIdCliente()),
            fn(CarritoCompra $carritoCompra) => !$carritoCompra->estaVacio()
        );

        return array_values(array_map(fn(CarritoCompra $carritoCompra) => $this->aArreglo($carritoCompra), $carritosCompra));
    }

    private function aArreglo(CarritoCompra $carritoCompra): array
    {
        $local = $this->localController->buscar($carritoCompra->getIdLocal());
        $localDisponible = $local !== null && $local->isActivo();

        $items = [];
        $total = 0.0;
        $todosDisponibles = true;

        foreach ($carritoCompra->getDetalles() as $detalle) {
            $item = $this->itemAArreglo($detalle, $carritoCompra->getIdLocal());
            $items[] = $item;

            if ($item["estado"] !== self::ESTADO_NO_DISPONIBLE) {
                $total += $item["subtotal"];
            }
            if ($item["estado"] !== self::ESTADO_OK) {
                $todosDisponibles = false;
            }
        }

        return [
            "idCarritoCompra" => $carritoCompra->getIdCarritoCompra(),
            "idLocal" => $carritoCompra->getIdLocal(),
            "localNombre" => $local?->getNombreLocal() ?? "Local no disponible",
            "localLogo" => $local?->getLogo(),
            "localDisponible" => $localDisponible,
            "actualizacionFecha" => $carritoCompra->getActualizacionFecha()?->format("Y-m-d H:i:s"),
            "cantidadProductos" => $carritoCompra->getCantidadProductos(),
            "cantidadArticulos" => $carritoCompra->getCantidadArticulos(),
            "total" => round($total, 2),
            "puedeConfirmar" => $localDisponible && $todosDisponibles && !$carritoCompra->estaVacio(),
            "items" => $items
        ];
    }

    private function itemAArreglo(CarritoCompraDetalle $detalle, int $idLocal): array
    {
        $producto = $this->productoRepository->obtenerPorId($detalle->getIdProducto());
        $cantidad = $detalle->getCantidad();

        if ($producto === null
            || !$producto->isActivo()
            || $producto->isVencido()
            || !$this->pedidoRepository->productoSeOfreceEnLocal($detalle->getIdProducto(), $idLocal)) {
            return [
                "idProducto" => $detalle->getIdProducto(),
                "nombre" => $producto?->getNombre() ?? "Producto eliminado",
                "imagen" => $producto?->getImagen(),
                "cantidad" => $cantidad,
                "precioOriginal" => null,
                "porcentajeDescuento" => null,
                "precioUnitario" => 0,
                "subtotal" => 0,
                "disponibles" => 0,
                "maximo" => 0,
                "estado" => self::ESTADO_NO_DISPONIBLE,
                "aviso" => "Este producto ya no está disponible. Quítalo para poder generar el pedido."
            ];
        }

        $disponibles = $producto->getCantidadDisponible();
        $precioUnitario = $producto->getPrecioFinal();

        if ($disponibles <= 0) {
            $estado = self::ESTADO_AGOTADO;
            $aviso = "Se agotó. Quítalo para poder generar el pedido.";
        } elseif ($disponibles < $cantidad) {
            $estado = self::ESTADO_INSUFICIENTE;
            $aviso = "Solo quedan {$disponibles} unidades. Baja la cantidad.";
        } else {
            $estado = self::ESTADO_OK;
            $aviso = null;
        }

        return [
            "idProducto" => $producto->getIdProducto(),
            "nombre" => $producto->getNombre(),
            "imagen" => $producto->getImagen(),
            "cantidad" => $cantidad,
            "precioOriginal" => $producto->getPrecioOriginal(),
            "porcentajeDescuento" => $producto->getPorcentajeDescuento(),
            "precioUnitario" => $precioUnitario,
            "subtotal" => round($precioUnitario * $cantidad, 2),
            "disponibles" => $disponibles,
            "maximo" => min($disponibles, CarritoCompraDetalle::CANTIDAD_MAXIMA),
            "estado" => $estado,
            "aviso" => $aviso
        ];
    }

    private function obtenerCarritoCompraOFallar(int $idCliente, int $idLocal): CarritoCompra
    {
        $carritoCompra = $this->carritoCompraRepository->buscarPorClienteYLocal($idCliente, $idLocal);
        if ($carritoCompra === null) {
            throw new InvalidArgumentException("No tienes un carrito en este local");
        }
        return $carritoCompra;
    }

    private function validarLocalParaComprar(array $usuarioSesion, int $idLocal): Local
    {
        $local = $this->localController->buscar($idLocal);
        if ($local === null || !$local->isActivo()) {
            throw new InvalidArgumentException("Este local no está disponible para recibir pedidos en este momento");
        }

        if ($usuarioSesion["tipo"] === Sesion::TIPO_COMERCIANTE
            && $this->localController->perteneceAComerciante($idLocal, $usuarioSesion["id"])) {
            throw new InvalidArgumentException("No puedes comprar en tu propio local");
        }

        return $local;
    }

    private function validarProductoDelLocal(int $idProducto, int $idLocal): Producto
    {
        $producto = $idProducto > 0 ? $this->productoRepository->obtenerPorId($idProducto) : null;

        if ($producto === null || !$producto->isActivo()) {
            throw new InvalidArgumentException("Este producto ya no está disponible");
        }

        if ($producto->isVencido()) {
            throw new InvalidArgumentException("La oferta de \"{$producto->getNombre()}\" ya terminó");
        }

        if (!$this->pedidoRepository->productoSeOfreceEnLocal($idProducto, $idLocal)) {
            throw new InvalidArgumentException("\"{$producto->getNombre()}\" no se vende en este local");
        }

        return $producto;
    }

    private function validarStock(Producto $producto, int $cantidadDeseada, int $yaEnCarritoCompra = 0): void
    {
        $disponibles = $producto->getCantidadDisponible();

        if ($disponibles <= 0) {
            throw new InvalidArgumentException("\"{$producto->getNombre()}\" está agotado");
        }

        if ($cantidadDeseada > $disponibles) {
            $mensaje = "Solo quedan {$disponibles} unidades de \"{$producto->getNombre()}\"";
            if ($yaEnCarritoCompra > 0) {
                $mensaje .= " y ya tienes {$yaEnCarritoCompra} en tu carrito";
            }
            throw new InvalidArgumentException($mensaje);
        }
    }
}