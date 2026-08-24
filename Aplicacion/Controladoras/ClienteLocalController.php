<?php

require_once __DIR__ . "/../Repositorios/ClienteLocalRepository.php";
require_once __DIR__ . "/../Modelos/ClienteLocal.php";

class ClienteLocalController
{
    private ClienteLocalRepository $clienteLocalRepository;

    public function __construct()
    {
        $this->clienteLocalRepository = new ClienteLocalRepository();
    }

    public function agregarFavorito(int $idCliente, int $idLocal): int|false
    {
        $clienteLocal = new ClienteLocal($idCliente, $idLocal);
        return $this->clienteLocalRepository->insertar($clienteLocal);
    }

    public function quitarFavorito(int $idCliente, int $idLocal): bool
    {
        return $this->clienteLocalRepository->eliminar($idCliente, $idLocal);
    }

    public function listarFavoritos(int $idCliente): array
    {
        return $this->clienteLocalRepository->obtenerLocalesPorCliente($idCliente);
    }
}