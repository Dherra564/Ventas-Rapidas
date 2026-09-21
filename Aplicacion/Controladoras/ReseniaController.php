<?php

require_once __DIR__ . "/../Repositorios/ReseniaRepository.php";
require_once __DIR__ . "/../Modelos/Resenia.php";

class ReseniaController
{
    private ReseniaRepository $reseniaRepository;

    public function __construct()
    {
        $this->reseniaRepository = new ReseniaRepository();
    }

    public function registrar(int $idCliente, int $idLocal, string $comentario, int $puntuacion): int|false
    {
        if (trim($comentario) === '') {
            throw new InvalidArgumentException('El comentario no puede estar vacío');
        }

        if ($puntuacion < 1 || $puntuacion > 5) {
            throw new InvalidArgumentException('La puntuación debe ser un número entero del 1 al 5');
        }

        $resenia = new Resenia($idCliente, $idLocal, $comentario, $puntuacion);
        return $this->reseniaRepository->registrar($resenia);
    }

    public function editar(Resenia $resenia): bool
    {
        if (trim($resenia->getComentario()) === '') {
            throw new InvalidArgumentException('El comentario no puede estar vacío');
        }

        if ($resenia->getPuntuacion() < 1 || $resenia->getPuntuacion() > 5) {
            throw new InvalidArgumentException('La puntuación debe ser un número entero del 1 al 5');
        }

        return $this->reseniaRepository->actualizar($resenia);
    }

    public function eliminar(int $idResenia): bool
    {
        return $this->reseniaRepository->eliminar($idResenia);
    }

    public function buscar(int $idResenia): ?Resenia
    {
        return $this->reseniaRepository->obtenerPorId($idResenia);
    }

    public function listarPorLocal(int $idLocal): array
    {
        return $this->reseniaRepository->obtenerPorLocal($idLocal);
    }

    public function listarPorCliente(int $idCliente): array
    {
        return $this->reseniaRepository->obtenerPorCliente($idCliente);
    }

    public function promedioPorLocal(int $idLocal): ?float
    {
        return $this->reseniaRepository->obtenerPromedioPorLocal($idLocal);
    }

    public function totalReseniasPorLocal(int $idLocal): int
    {
        return $this->reseniaRepository->contarPorLocal($idLocal);
    }
}
