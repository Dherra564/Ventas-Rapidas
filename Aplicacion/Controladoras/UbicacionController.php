<?php

require_once __DIR__ . "/../Repositorios/UbicacionRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialUbicacionRepository.php";
require_once __DIR__ . "/../Modelos/HistorialUbicacion.php";

class UbicacionController
{
    private UbicacionRepository $ubicacionRepository;
    private HistorialUbicacionRepository $historialUbicacionRepository;

    public function __construct()
    {
        $this->ubicacionRepository = new UbicacionRepository();
        $this->historialUbicacionRepository = new HistorialUbicacionRepository();
    }

    public function registrarUbicacionLogin(int $idUsuario, string $tipoUsuario, float $latitud, float $longitud): void
    {
        $idUbicacion = null;
        $valorAnterior = null;

        if ($tipoUsuario === HistorialUbicacion::TIPO_CLIENTE) {
            $ubicacionCliente = $this->ubicacionRepository->obtenerPorCliente($idUsuario);

            if ($ubicacionCliente !== null) {
                $idUbicacion = $ubicacionCliente->getIdUbicacion();
                if ($ubicacionCliente->tieneCoordenadas()) {
                    $valorAnterior = $ubicacionCliente->getLatitud() . ',' . $ubicacionCliente->getLongitud();
                }
                $this->ubicacionRepository->actualizarCoordenadasCliente($idUsuario, $latitud, $longitud);
            }
        } else {
            $ultimo = $this->historialUbicacionRepository->obtenerUltimoPorUsuario($idUsuario, $tipoUsuario);
            if ($ultimo !== null) {
                $valorAnterior = $ultimo->getValorNuevo();
            }
        }

        $valorNuevo = $latitud . ',' . $longitud;

        $historial = new HistorialUbicacion(
            $idUbicacion,
            $idUsuario,
            $tipoUsuario,
            'coordenadasLogin',
            $valorAnterior,
            $valorNuevo
        );

        $this->historialUbicacionRepository->registrar($historial);
    }

    public function listarHistorialPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        return $this->historialUbicacionRepository->obtenerPorUsuario($idUsuario, $tipoUsuario);
    }
}