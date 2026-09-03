<?php

require_once __DIR__ . "/../Repositorios/PasswordHistorialRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialFotoPerfilRepository.php";

class HistorialController
{
    private PasswordHistorialRepository $historialPasswordRepository;
    private HistorialFotoPerfilRepository $historialFotoPerfilRepository;

    public function __construct()
    {
        $this->historialPasswordRepository = new PasswordHistorialRepository();
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