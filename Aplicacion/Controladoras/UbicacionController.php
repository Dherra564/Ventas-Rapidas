<?php

require_once __DIR__ . "/../Repositorios/UbicacionRepository.php";
require_once __DIR__ . "/../Repositorios/UbicacionHistorialRepository.php";
require_once __DIR__ . "/../Modelos/UbicacionHistorial.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";

class UbicacionController
{
    private UbicacionRepository $ubicacionRepository;
    private UbicacionHistorialRepository $historialUbicacionRepository;

    public function __construct()
    {
        $this->ubicacionRepository = new UbicacionRepository();
        $this->historialUbicacionRepository = new UbicacionHistorialRepository();
    }

    public function registrarUbicacionLogin(int $idUsuario, string $tipoUsuario, float $latitud, float $longitud): void
    {
        $idUbicacion = null;
        $latitudAnterior = null;
        $longitudAnterior = null;

        if ($tipoUsuario === UbicacionHistorial::TIPO_CLIENTE) {
            $ubicacionCliente = $this->ubicacionRepository->obtenerPorCliente($idUsuario);

            if ($ubicacionCliente !== null) {
                $idUbicacion = $ubicacionCliente->getIdUbicacion();
                if ($ubicacionCliente->tieneCoordenadas()) {
                    $latitudAnterior = $ubicacionCliente->getLatitud();
                    $longitudAnterior = $ubicacionCliente->getLongitud();
                }
                $this->ubicacionRepository->actualizarCoordenadasCliente($idUsuario, $latitud, $longitud);
            }
        } else {
            $ultimo = $this->historialUbicacionRepository->obtenerUltimoPorUsuario($idUsuario, $tipoUsuario);
            if ($ultimo !== null) {
                $latitudAnterior = $ultimo->getLatitudNueva();
                $longitudAnterior = $ultimo->getLongitudNueva();
            }
        }

        $historial = new UbicacionHistorial(
            $idUbicacion,
            $idUsuario,
            $tipoUsuario,
            $latitudAnterior,
            $longitudAnterior,
            $latitud,
            $longitud
        );

        $this->historialUbicacionRepository->registrar($historial);
    }

    public function listarHistorialPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        return $this->historialUbicacionRepository->obtenerPorUsuario($idUsuario, $tipoUsuario);
    }

    public function buscarPorCliente(int $idCliente): ?Ubicacion
    {
        return $this->ubicacionRepository->obtenerPorCliente($idCliente);
    }

        public function actualizarUbicacionCliente(
        int $idCliente,
        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia,
        ?float $latitud = null,
        ?float $longitud = null
    ): bool {
        $actual = $this->ubicacionRepository->obtenerPorCliente($idCliente);

        $ubicacion = new Ubicacion(
            null,
            $idProvincia,
            $idCanton,
            $idDistrito,
            $direccionExacta,
            $referencia,
            $idCliente,
            true,
            $actual?->getIdUbicacion() ?? 0,
            $latitud ?? $actual?->getLatitud(),
            $longitud ?? $actual?->getLongitud()
        );

        return $this->ubicacionRepository->actualizar($ubicacion);
    }
}