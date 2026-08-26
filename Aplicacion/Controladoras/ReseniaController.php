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

    public function registrar(
        int $idCliente,
        int $idLocal,
        string $comentario,
        int $puntuacion
    ): int|false {

        if ($this->reseniaRepository->existeResenia($idCliente, $idLocal)) {
            throw new Exception("Este cliente ya registró una reseña para este local");
        }

        $resenia = new Resenia(
            $idCliente,
            $idLocal,
            $comentario,
            $puntuacion
        );

        return $this->reseniaRepository->registrar($resenia);
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

    public function editar(Resenia $resenia): bool
    {
        return $this->reseniaRepository->actualizar($resenia);
    }

    public function eliminar(int $idResenia): bool
    {
        return $this->reseniaRepository->eliminar($idResenia);
    }

    public function promedioPorLocal(int $idLocal): ?float
    {
        return $this->reseniaRepository->obtenerPromedioPorLocal($idLocal);
    }

    public function totalReseniasPorLocal(int $idLocal): int
    {
        return $this->reseniaRepository->contarPorLocal($idLocal);
    }

    public function existeReseniaDeCliente(int $idCliente, int $idLocal): bool
    {
        return $this->reseniaRepository->existeResenia($idCliente, $idLocal);
    }
}