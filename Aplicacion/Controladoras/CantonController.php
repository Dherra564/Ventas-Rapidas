<?php

require_once __DIR__ . "/../Repositorios/CantonRepository.php";
require_once __DIR__ . "/../Modelos/Canton.php";

class CantonController
{
    private CantonRepository $cantonRepository;

    public function __construct()
    {
        $this->cantonRepository = new CantonRepository();
    }

    public function listar(): array
    {
        return $this->cantonRepository->obtenerTodos();
    }

    public function buscar(int $idCanton): ?Canton
    {
        return $this->cantonRepository->obtenerPorId($idCanton);
    }

    // Para el select en cascada: cantones que pertenecen a una provincia
    public function listarPorProvincia(int $idProvincia): array
    {
        return $this->cantonRepository->obtenerPorProvincia($idProvincia);
    }
}