<?php

require_once __DIR__ . "/../Repositorios/ResenaRepository.php";
require_once __DIR__ . "/../Modelos/Resena.php";

class ResenaController
{
    private ResenaRepository $resenaRepository;

    public function __construct()
    {
        $this->resenaRepository = new ResenaRepository();
    }

    public function registrar(
        int $idCliente,
        int $idLocal,
        string $comentario,
        int $puntuacion
    ): int|false {

        if ($this->resenaRepository->existeResena($idCliente, $idLocal)) {
            throw new Exception("Este cliente ya registró una reseña para este local");
        }

        $resena = new Resena(
            $idCliente,
            $idLocal,
            $comentario,
            $puntuacion
        );

        return $this->resenaRepository->registrar($resena);
    }

    public function buscar(int $idResena): ?Resena
    {
        return $this->resenaRepository->obtenerPorId($idResena);
    }

    public function listarPorLocal(int $idLocal): array
    {
        return $this->resenaRepository->obtenerPorLocal($idLocal);
    }

    public function listarPorCliente(int $idCliente): array
    {
        return $this->resenaRepository->obtenerPorCliente($idCliente);
    }

    public function editar(Resena $resena): bool
    {
        return $this->resenaRepository->actualizar($resena);
    }

    public function eliminar(int $idResena): bool
    {
        return $this->resenaRepository->eliminar($idResena);
    }

    public function promedioPorLocal(int $idLocal): ?float
    {
        return $this->resenaRepository->obtenerPromedioPorLocal($idLocal);
    }

    public function totalResenasPorLocal(int $idLocal): int
    {
        return $this->resenaRepository->contarPorLocal($idLocal);
    }

    public function existeResenaDeCliente(int $idCliente, int $idLocal): bool
    {
        return $this->resenaRepository->existeResena($idCliente, $idLocal);
    }
}