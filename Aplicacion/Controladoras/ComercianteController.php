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
        string $numeroIdentificacion,
        string $correo,
        string $password,
        string $fotoPerfil = ''
    ): int|false {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $comerciante = new Comerciante(
            $nombre,
            $alias,
            $numeroIdentificacion,
            $correo,
            $passwordHash,
            $fotoPerfil
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

    public function existeIdentificacion(string $numeroIdentificacion): bool
    {
        return $this->comercianteRepository->existeIdentificacion($numeroIdentificacion);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->comercianteRepository->existeCorreo($correo);
    }

    public function buscarPorIdentificacion(string $numeroIdentificacion): ?Comerciante
    {
        return $this->comercianteRepository->obtenerPorIdentificacion($numeroIdentificacion);
    }

}