<?php

require_once __DIR__ . "/../Repositorios/ProductoLocalRepository.php";
require_once __DIR__ . "/../Repositorios/LocalRepository.php";
require_once __DIR__ . "/../Modelos/ProductoLocal.php";

class ProductoLocalController
{
    private ProductoLocalRepository $productoLocalRepository;
    private LocalRepository $localRepository;

    public function __construct()
    {
        $this->productoLocalRepository = new ProductoLocalRepository();
        $this->localRepository = new LocalRepository();
    }

    public function agregar(int $idProducto, int $idLocal): int|false
    {
        $productoLocal = new ProductoLocal($idProducto, $idLocal);
        return $this->productoLocalRepository->insertar($productoLocal);
    }

    public function quitar(int $idProductoLocal): bool
    {
        return $this->productoLocalRepository->eliminar($idProductoLocal);
    }

    // Devuelve la relación cruda (idProductoLocal, idProducto, idLocal) para
    // poder validar pertenencia antes de una acción sensible como quitar().
    public function buscar(int $idProductoLocal): ?array
    {
        return $this->productoLocalRepository->obtenerPorId($idProductoLocal);
    }

    // Devuelve los locales adicionales donde también se ofrece este producto, con nombre incluido, listos para mostrar.

    public function listarPorProducto(int $idProducto): array
    {
        $relaciones = $this->productoLocalRepository->obtenerRelacionesPorProducto($idProducto);

        $resultado = [];

        foreach ($relaciones as $relacion) {
            $local = $this->localRepository->obtenerPorId((int) $relacion['tblocalid']);

            $resultado[] = [
                'idProductoLocal' => (int) $relacion['tbproductolocalid'],
                'idLocal' => (int) $relacion['tblocalid'],
                'nombreLocal' => $local?->getNombreLocal() ?? '(local no encontrado)'
            ];
        }

        return $resultado;
    }
}