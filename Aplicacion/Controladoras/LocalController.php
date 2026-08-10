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
        int $idComerciante,
        int $idTipoLocal,
        string $nombreLocal,
        string $telefono,
        string $correo,
        ?string $descripcion,
        ?string $productosAOfrecer,
        ?string $logo,

        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia

    ): int|false {

        $local = new Local(
            $idTipoLocal,
            $nombreLocal,
            $telefono,
            $correo,
            $descripcion,
            $productosAOfrecer,
            $logo
        );

        $ubicacion = new Ubicacion(
            0,
            $idProvincia,
            $idCanton,
            $idDistrito,
            $direccionExacta,
            $referencia
        );

        return $this->localRepository->insertar($local, $ubicacion, $idComerciante);
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

    public function editar(Local $local): bool
    {
        return $this->localRepository->actualizar($local);
    }

    public function eliminar(int $idLocal): bool
    {
        return $this->localRepository->eliminar($idLocal);
    }

    public function buscarConFiltros(?string $nombre = null, ?int $idTipoLocal = null, ?bool $activo = null): array
    {
        return $this->localRepository->buscar($nombre, $idTipoLocal, $activo);
    }
}
