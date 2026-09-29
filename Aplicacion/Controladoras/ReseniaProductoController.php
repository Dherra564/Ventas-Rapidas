<?php

require_once __DIR__ . "/../Repositorios/ReseniaProductoRepository.php";
require_once __DIR__ . "/../Modelos/ReseniaProducto.php";

class ReseniaProductoController
{
    private ReseniaProductoRepository $repositorio;

    public function __construct()
    {
        $this->repositorio = new ReseniaProductoRepository();
    }

    public function registrar(int $idCliente, int $idProducto, string $comentario, int $puntuacion): int|false
    {
        if (trim($comentario) === '') {
            throw new InvalidArgumentException('El comentario no puede estar vacío');
        }
        if ($puntuacion < 1 || $puntuacion > 5) {
            throw new InvalidArgumentException('La puntuación debe ser un número entero del 1 al 5');
        }

        $resenia = new ReseniaProducto($idCliente, $idProducto, $comentario, $puntuacion);
        return $this->repositorio->registrar($resenia);
    }

    public function editar(ReseniaProducto $resenia): bool
    {
        return $this->repositorio->actualizar($resenia);
    }

    public function eliminar(int $id): bool
    {
        return $this->repositorio->eliminar($id);
    }

    public function buscar(int $id): ?ReseniaProducto
    {
        return $this->repositorio->obtenerPorId($id);
    }

    public function listarPorProducto(int $idProducto): array
    {
        return $this->repositorio->obtenerPorProducto($idProducto);
    }

    public function listarPorCliente(int $idCliente): array
    {
        return $this->repositorio->obtenerPorCliente($idCliente);
    }

    public function promedioPorProducto(int $idProducto): ?float
    {
        return $this->repositorio->obtenerPromedioPorProducto($idProducto);
    }

    public function totalPorProducto(int $idProducto): int
    {
        return $this->repositorio->contarPorProducto($idProducto);
    }

    public function yaReseno(int $idCliente, int $idProducto): bool
    {
        return $this->repositorio->existeResenia($idCliente, $idProducto);
    }
}