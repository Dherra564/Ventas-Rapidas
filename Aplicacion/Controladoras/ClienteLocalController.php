<?php

require_once __DIR__ . "/../Repositorios/ClienteLocalRepository.php";
require_once __DIR__ . "/../Repositorios/LocalRepository.php";
require_once __DIR__ . "/../Modelos/ClienteLocal.php";

class ClienteLocalController
{
    private ClienteLocalRepository $clienteLocalRepository;
    private LocalRepository $localRepository;

    public function __construct()
    {
        $this->clienteLocalRepository = new ClienteLocalRepository();
        $this->localRepository = new LocalRepository();
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

    public function seguir(int $idCliente, int $idLocal): int|false
    {
        return $this->agregarFavorito($idCliente, $idLocal);
    }

    public function dejarDeSeguir(int $idClienteLocal): bool
    {
        return $this->clienteLocalRepository->eliminarPorId($idClienteLocal);
    }

    public function listarPorCliente(int $idCliente): array
    {
        $relaciones = $this->clienteLocalRepository->obtenerRelacionesPorCliente($idCliente);
        $resultado = [];

        foreach ($relaciones as $relacion) {
            $local = $this->localRepository->obtenerPorId((int) $relacion['tblocalid']);
            $resultado[] = [
                'idClienteLocal' => (int) $relacion['tbclientelocalid'],
                'idLocal' => (int) $relacion['tblocalid'],
                'nombreLocal' => $local?->getNombreLocal() ?? '(local no encontrado)'
            ];
        }

        return $resultado;
    }

    public function perteneceACliente(int $idClienteLocal, int $idCliente): bool
    {
        return $this->clienteLocalRepository->perteneceACliente($idClienteLocal, $idCliente);
    }
}