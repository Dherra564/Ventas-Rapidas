<?php

require_once __DIR__ . "/../Repositorios/HistorialPasswordRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialFotoPerfilRepository.php";

class HistorialController
{
    private HistorialPasswordRepository $historialPasswordRepository;
    private HistorialFotoPerfilRepository $historialFotoPerfilRepository;

    public function __construct()
    {
        $this->historialPasswordRepository = new HistorialPasswordRepository();
        $this->historialFotoPerfilRepository = new HistorialFotoPerfilRepository();
    }

    public function listarPasswords(int $idUsuario, string $tipoUsuario): array
    {
        return $this->historialPasswordRepository->obtenerPorUsuario($idUsuario, $tipoUsuario);
    }

    public function listarFotos(int $idUsuario, string $tipoUsuario): array
    {
        return $this->historialFotoPerfilRepository->obtenerPorUsuario($idUsuario, $tipoUsuario);
    }
}
