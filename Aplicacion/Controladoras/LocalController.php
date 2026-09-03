<?php

require_once __DIR__ . "/../Repositorios/LocalRepository.php";
require_once __DIR__ . "/../Repositorios/TipoLocalRepository.php";
require_once __DIR__ . "/../Repositorios/SesionActicoHistoricoRepository.php";
require_once __DIR__ . "/../Repositorios/ComercianteLocalRepository.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Modelos/TipoLocal.php";
require_once __DIR__ . "/../Modelos/SesionActivaHistorico.php";

class LocalController
{
    private LocalRepository $localRepository;
    private TipoLocalRepository $tipoLocalRepository;
    private SesionActicoHistoricoRepository $historialActividadRepository;
    private ComercianteLocalRepository $comercianteLocalRepository;

    public function __construct()
    {
        $this->localRepository = new LocalRepository();
        $this->tipoLocalRepository = new TipoLocalRepository();
        $this->historialActividadRepository = new SesionActicoHistoricoRepository();
        $this->comercianteLocalRepository = new ComercianteLocalRepository();
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
        ?string $referencia,
        ?float $latitud = null,
        ?float $longitud = null

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
            $referencia,
            null,
            true,
            0,
            $latitud,
            $longitud
        );

        $idLocal = $this->localRepository->insertar($local, $ubicacion, $idComerciante);

        if ($idLocal !== false) {
            try {
                $this->historialActividadRepository->registrarEntradaPerfil($idComerciante, $idLocal);
            } catch (Exception $e) {
            }
        }

        return $idLocal;
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

    public function perteneceAComerciante(int $idLocal, int $idComerciante): bool
    {
        return $this->comercianteLocalRepository->obtenerComerciantePorLocal($idLocal) === $idComerciante;
    }

    public function entrarPerfil(int $idLocal, int $idComerciante): bool
    {
        $duenoReal = $this->comercianteLocalRepository->obtenerComerciantePorLocal($idLocal);

        if ($duenoReal !== $idComerciante) {
            throw new InvalidArgumentException("Este local no pertenece a tu cuenta");
        }

        $this->localRepository->reactivar($idLocal);

        try {
            $this->historialActividadRepository->registrarEntradaPerfil($idComerciante, $idLocal);
        } catch (Exception $e) {
        }

        return true;
    }

    public function listarPorComerciante(int $idComerciante): array
    {
        $this->sincronizarActividad();

        $idsLocales = $this->comercianteLocalRepository->obtenerLocalesPorComerciante($idComerciante);

        $locales = [];
        foreach ($idsLocales as $idLocal) {
            $local = $this->localRepository->obtenerPorId($idLocal);
            if ($local !== null) {
                $locales[] = $local;
            }
        }

        return $locales;
    }

    public function sincronizarActividad(int $dias = 7): int
    {
        return $this->localRepository->sincronizarActivoPorInactividad($dias);
    }

    public function obtenerHistorialActividad(int $idLocal): array
    {
        return $this->historialActividadRepository->obtenerPorLocal($idLocal);
    }

    public function estaActivoPorActividad(int $idLocal, int $dias = 7): bool
    {
        return $this->historialActividadRepository->tieneActividadReciente($idLocal, $dias);
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

    public function buscarSimilares(string $nombre, ?int $idLocalExcluir = null): array
    {
        if (trim($nombre) === "") {
            return [];
        }

        return $this->localRepository->buscarSimilares($nombre, $idLocalExcluir);
    }

}