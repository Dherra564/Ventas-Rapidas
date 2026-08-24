<?php

require_once __DIR__ . "/../Repositorios/LocalRepository.php";
require_once __DIR__ . "/../Repositorios/TipoLocalRepository.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Modelos/TipoLocal.php";

class LocalController
{
    private LocalRepository $localRepository;
    private TipoLocalRepository $tipoLocalRepository;

    public function __construct()
    {
        $this->localRepository = new LocalRepository();
        $this->tipoLocalRepository = new TipoLocalRepository();
    }

       public function registrar(
        int $idComerciante,
        string $nombreTipoLocal,
        string $nombreLocal,
        string $telefono,
        string $correo,
        ?string $descripcion,
        ?string $logo,

        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia

    ): int|false {

        $idTipoLocal = $this->resolverOCrearTipoLocal($nombreTipoLocal);

        $local = new Local(
            $idTipoLocal,
            $nombreLocal,
            $telefono,
            $correo,
            $descripcion,
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

    public function buscarConFiltros(
        ?string $nombre = null,
        ?int $idTipoLocal = null,
        ?int $idProvincia = null,
        ?int $idCanton = null,
        ?int $idDistrito = null,
        ?bool $activo = null
    ): array {
        return $this->localRepository->buscar($nombre, $idTipoLocal, $idProvincia, $idCanton, $idDistrito, $activo);
    }

    public function buscarTiposCoincidentes(string $textoParcial): array
    {
        if (trim($textoParcial) === "") {
            return [];
        }

        return $this->tipoLocalRepository->buscarPorNombre($textoParcial);
    }

    private function resolverOCrearTipoLocal(string $nombreTipoLocal): int
    {
        $nombreNormalizado = trim($nombreTipoLocal);

        $existente = $this->tipoLocalRepository->obtenerPorNombreExacto($nombreNormalizado);

        if ($existente !== null) {
            return $existente->getIdTipoLocal();
        }

        $nuevoTipo = new TipoLocal($nombreNormalizado);
        $id = $this->tipoLocalRepository->insertar($nuevoTipo);

        if ($id === false) {
            throw new Exception("No se pudo registrar el nuevo tipo de local");
        }

        return $id;
    }

    public function existeNombreLocal(string $nombreLocal): bool
    {
        return $this->localRepository->existeNombre($nombreLocal);
    }

    public function buscarTipoLocal(int $idTipoLocal): ?TipoLocal
    {
        return $this->tipoLocalRepository->obtenerPorId($idTipoLocal);
    }

    public function resolverTipoLocal(string $nombreTipoLocal): int
    {
        return $this->resolverOCrearTipoLocal($nombreTipoLocal);
    }

    public function existeCorreoLocal(string $correo): bool
    {
        return $this->localRepository->existeCorreo($correo);
    }

}