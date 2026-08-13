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

    public function seguir(int $idCliente, int $idLocal): int|false
    {
        $clienteLocal = new ClienteLocal($idCliente, $idLocal);
        return $this->clienteLocalRepository->insertar($clienteLocal);
    }

    public function dejarDeSeguir(int $idClienteLocal): bool
    {
        return $this->clienteLocalRepository->eliminar($idClienteLocal);
    }

    // Devuelve los locales que sigue este cliente, con nombre incluido.
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
}