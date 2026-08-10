<?php

require_once __DIR__ . "/../Repositorios/ComercianteRepository.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";

class ComercianteController
{
    private ComercianteRepository $comercianteRepository;

    public function __construct()
    {
        $this->comercianteRepository = new ComercianteRepository();
    }

    public function registrar(
        string $nombre,
        string $alias,
        string $cedula,
        string $correo,
        string $password
    ): int|false {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $comerciante = new Comerciante(
            $nombre,
            $alias,
            $cedula,
            $correo,
            $passwordHash
        );

        return $this->comercianteRepository->insertar($comerciante);
    }

    public function listar(): array
    {
        return $this->comercianteRepository->obtenerTodos();
    }

    public function buscar(int $idComerciante): ?Comerciante
    {
        return $this->comercianteRepository->obtenerPorId($idComerciante);
    }

    public function editar(Comerciante $comerciante): bool
    {
        return $this->comercianteRepository->actualizar($comerciante);
    }

    public function eliminar(int $idComerciante): bool
    {
        return $this->comercianteRepository->eliminar($idComerciante);
    }
}