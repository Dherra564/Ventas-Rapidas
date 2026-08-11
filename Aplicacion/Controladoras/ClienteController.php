<?php

require_once __DIR__ . "/../Repositorios/ClienteRepository.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";

class ClienteController
{
    private ClienteRepository $clienteRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
    }

    public function registrar(
        string $nombreCompleto,
        string $numeroIdentificacion,
        string $correo,
        string $password,
        string $fotoPerfil,

        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia

    ): int|false {

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $cliente = new Cliente(
            $nombreCompleto,
            $numeroIdentificacion,
            $correo,
            $passwordHash,
            $fotoPerfil
        );

        $ubicacion = new Ubicacion(
            $idProvincia,
            $idCanton,
            $idDistrito,
            $direccionExacta,
            0,
            0,
            $referencia
        );

        return $this->clienteRepository->insertarConUbicacion($cliente, $ubicacion);
    }

    public function listar(): array
    {
        return $this->clienteRepository->obtenerTodos();
    }

    public function buscar(int $idCliente): ?Cliente
    {
        return $this->clienteRepository->obtenerPorId($idCliente);
    }

    public function buscarConUbicacion(int $idCliente): ?array
    {
        return $this->clienteRepository->obtenerClienteConUbicacion($idCliente);
    }

    public function editar(Cliente $cliente): bool
    {
        return $this->clienteRepository->actualizar($cliente);
    }

    public function eliminar(int $idCliente): bool
    {
        return $this->clienteRepository->eliminar($idCliente);
    }

    public function buscarConFiltros(?string $nombre = null, ?bool $activo = null): array
    {
        return $this->clienteRepository->buscar($nombre, $activo);
    }

    public function existeIdentificacion(string $numeroIdentificacion): bool
    {
        return $this->clienteRepository->existeIdentificacion($numeroIdentificacion);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->clienteRepository->existeCorreo($correo);
    }

    public function buscarPorIdentificacion(string $numeroIdentificacion): ?Cliente
    {
        return $this->clienteRepository->obtenerPorIdentificacion($numeroIdentificacion);
    }
}