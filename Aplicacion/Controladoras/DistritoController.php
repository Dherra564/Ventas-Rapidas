<?php

require_once __DIR__ . "/../Repositorios/DistritoRepository.php";
require_once __DIR__ . "/../Modelos/Distrito.php";

class DistritoController
{
    private DistritoRepository $distritoRepository;

    public function __construct()
    {
        $this->distritoRepository = new DistritoRepository();
    }

    public function listar(): array
    {
        return $this->distritoRepository->obtenerTodos();
    }

    public function buscar(int $idDistrito): ?Distrito
    {
        return $this->distritoRepository->obtenerPorId($idDistrito);
    }

    public function listarPorCanton(int $idCanton): array
    {
        return $this->distritoRepository->obtenerPorCanton($idCanton);
    }
}