<?php

require_once __DIR__ . "/../Repositorios/ProductoRepository.php";
require_once __DIR__ . "/../Repositorios/ProductoLocalRepository.php";
require_once __DIR__ . "/../Repositorios/TipoProductoRepository.php";
require_once __DIR__ . "/../Modelos/Producto.php";
require_once __DIR__ . "/../Modelos/TipoProducto.php";

class ProductoController
{
    private ProductoRepository $productoRepository;
    private TipoProductoRepository $tipoProductoRepository;
    private ProductoLocalRepository $productoLocalRepository;

    public function __construct()
    {
        $this->productoRepository = new ProductoRepository();
        $this->tipoProductoRepository = new TipoProductoRepository();
        $this->productoLocalRepository = new ProductoLocalRepository();
    }

    public function registrar(
        int $idLocal,
        string $nombreTipoProducto,
        string $nombre,
        float $precioOriginal,
        ?float $porcentajeDescuento,
        ?string $descripcion,
        int $cantidadDisponible,
        ?string $imagen
    ): int|false {

        $idTipoProducto = $this->resolverOCrearTipoProducto($nombreTipoProducto);

        $producto = new Producto(
            $idLocal,
            $idTipoProducto,
            $nombre,
            $precioOriginal,
            $porcentajeDescuento,
            $descripcion,
            $cantidadDisponible,
            $imagen
        );

        return $this->productoRepository->insertar($producto);
    }

    public function listar(): array
    {
        return $this->productoRepository->obtenerTodos();
    }

    public function buscar(int $idProducto): ?Producto
    {
        return $this->productoRepository->obtenerPorId($idProducto);
    }

    public function listarPorLocal(int $idLocal): array
    {
        $propios = $this->productoRepository->obtenerPorLocal($idLocal);

        $idsCompartidos = $this->productoLocalRepository->obtenerProductosPorLocal($idLocal);

        $idsYaIncluidos = array_map(fn($p) => $p->getIdProducto(), $propios);

        foreach ($idsCompartidos as $idProductoCompartido) {
            if (in_array((int) $idProductoCompartido, $idsYaIncluidos, true)) {
                continue;
            }

            $producto = $this->productoRepository->obtenerPorId((int) $idProductoCompartido);

            if ($producto !== null) {
                $propios[] = $producto;
            }
        }

        return $propios;
    }

    public function buscarConFiltros(
        ?string $nombre = null,
        ?int $idLocal = null,
        ?int $idTipoProducto = null,
        ?float $precioMinimo = null,
        ?float $precioMaximo = null,
        ?bool $activo = null
    ): array {
        return $this->productoRepository->buscar($nombre, $idLocal, $idTipoProducto, $precioMinimo, $precioMaximo, $activo);
    }

    public function editar(Producto $producto): bool
    {
        return $this->productoRepository->actualizar($producto);
    }

    public function eliminar(int $idProducto): bool
    {
        return $this->productoRepository->eliminar($idProducto);
    }

    public function buscarTiposCoincidentes(string $textoParcial): array
    {
        if (trim($textoParcial) === "") {
            return [];
        }

        return $this->tipoProductoRepository->buscarPorNombre($textoParcial);
    }

    public function buscarTipoProducto(int $idTipoProducto): ?TipoProducto
    {
        return $this->tipoProductoRepository->obtenerPorId($idTipoProducto);
    }

    public function resolverTipoProducto(string $nombreTipoProducto): int
    {
        return $this->resolverOCrearTipoProducto($nombreTipoProducto);
    }

    private function resolverOCrearTipoProducto(string $nombreTipoProducto): int
    {
        $nombreNormalizado = trim($nombreTipoProducto);

        $existente = $this->tipoProductoRepository->obtenerPorNombreExacto($nombreNormalizado);

        if ($existente !== null) {
            return $existente->getIdTipoProducto();
        }

        $nuevoTipo = new TipoProducto($nombreNormalizado);
        $id = $this->tipoProductoRepository->insertar($nuevoTipo);

        if ($id === false) {
            throw new Exception("No se pudo registrar el nuevo tipo de producto");
        }

        return $id;
    }
}