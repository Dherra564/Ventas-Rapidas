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

    public function buscarConFiltros(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        return $this->comercianteRepository->buscar($nombre, $alias, $activo);
    }

    public function existeCedula(string $cedula): bool
    {
        return $this->comercianteRepository->existeCedula($cedula);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->comercianteRepository->existeCorreo($correo);
    }

    public function buscarPorCedula(string $cedula): ?Comerciante
    {
        return $this->comercianteRepository->obtenerPorCedula($cedula);
    }
    
}
