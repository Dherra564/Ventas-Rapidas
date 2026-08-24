<?php

require_once __DIR__ . "/../Repositorios/RegistroCompraRepository.php";
require_once __DIR__ . "/../Modelos/RegistroCompra.php";

class RegistroCompraController
{
    private RegistroCompraRepository $registroCompraRepository;

    public function __construct()
    {
        $this->registroCompraRepository = new RegistroCompraRepository();
    }

    public function registrar(int $idCliente, int $idLocal): int|false
    {
        $registro = new RegistroCompra($idCliente, $idLocal);
        return $this->registroCompraRepository->registrar($registro);
    }

    public function listarPorCliente(int $idCliente): array
    {
        return $this->registroCompraRepository->obtenerPorCliente($idCliente);
    }

    public function listarPorLocalYFecha(int $idLocal, string $fecha): array
    {
        return $this->registroCompraRepository->obtenerPorLocalYFecha($idLocal, $fecha);
    }

    public function listarPorClienteYFecha(int $idCliente, string $fecha): array
    {
        return $this->registroCompraRepository->obtenerPorClienteYFecha($idCliente, $fecha);
    }

    public function localesMasComprados(int $limite = 10): array
    {
        return $this->registroCompraRepository->obtenerLocalesMasComprados($limite);
    }

    public function contarComprasPorLocal(int $idLocal): int
    {
        return $this->registroCompraRepository->contarComprasPorLocal($idLocal);
    }
}