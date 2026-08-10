<?php

require_once __DIR__ . "/../Repositorios/ProvinciaRepository.php";
require_once __DIR__ . "/../Modelos/Provincia.php";

class ProvinciaController
{
    private ProvinciaRepository $provinciaRepository;

    public function __construct()
    {
        $this->provinciaRepository = new ProvinciaRepository();
    }

    public function listar(): array
    {
        return $this->provinciaRepository->obtenerTodos();
    }

    public function buscar(int $idProvincia): ?Provincia
    {
        return $this->provinciaRepository->obtenerPorId($idProvincia);
    }
}