<?php

require_once __DIR__ . "/../Repositorios/LocalRepository.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";


class LocalController
{
    private LocalRepository $localRepository;

    public function __construct()
    {
        $this->localRepository = new LocalRepository();
    }

    public function registrar(
        int $idProveedor,
        string $nombreLocal,
        ?string $descripcion,
        string $telefono,
        string $correo,
        ?string $imagen,

        string $provincia,
        string $canton,
        string $distrito,
        string $direccionExacta,
        ?string $referencia

    ): bool {

        $local = new Local(
            $idProveedor,
            $nombreLocal,
            $telefono,
            $correo,
            $descripcion,
            $imagen
        );

        $ubicacion = new Ubicacion(
            0,
            $provincia,
            $canton,
            $distrito,
            $direccionExacta,
            $referencia
        );

        return $this->localRepository->insertar(
            $local,
            $ubicacion
        );

    }

    public function listar(): array
    {
        return $this->localRepository->obtenerTodos();
    }

    public function buscar(int $idLocal): ?Local
    {
        return $this->localRepository->obtenerPorId($idLocal);
    }

    public function buscarConUbicacion(int $idLocal): ?array
    {
        return $this->localRepository->obtenerLocalConUbicacion($idLocal);
    }

    public function editar(
        Local $local,
        Ubicacion $ubicacion
    ): bool {

        return $this->localRepository->actualizar(
            $local,
            $ubicacion
        );

    }

    public function eliminar(int $idLocal): bool
    {
        return $this->localRepository->eliminar($idLocal);
    }

    public function existeNombreLocal(string $nombreLocal): bool
    {
        return $this->localRepository->existeNombre($nombreLocal);
    }

}