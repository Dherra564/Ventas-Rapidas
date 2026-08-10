<?php

require_once __DIR__ . "/../Repositorios/ProductoRepository.php";
require_once __DIR__ . "/../Modelos/Producto.php";

class ProductoController
{
    private ProductoRepository $productoRepository;

    public function __construct()
    {
        $this->productoRepository = new ProductoRepository();
    }

    public function registrar(
        int $idLocal,
        int $idTipoProducto,
        string $nombre,
        float $precioOriginal,
        ?float $porcentajeDescuento,
        ?string $descripcion,
        int $cantidadDisponible,
        ?string $imagen
    ): int|false {

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
        return $this->productoRepository->obtenerPorLocal($idLocal);
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
}