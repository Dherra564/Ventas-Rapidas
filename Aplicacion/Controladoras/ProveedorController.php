<?php

require_once __DIR__ . "/../Repositorios/ProveedorRepository.php";
require_once __DIR__ . "/../Modelos/Proveedor.php";


class ProveedorController
{

    private ProveedorRepository $proveedorRepository;

    public function __construct()
    {
        $this->proveedorRepository = new ProveedorRepository();
    }

    public function registrar(
        string $nombre,
        string $apellido,
        string $cedula,
        string $correo,
        string $password
    ): bool {


        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $proveedor = new Proveedor(

            $nombre,
            $apellido,
            $cedula,
            $correo,
            $passwordHash

        );


        return $this->proveedorRepository->insertar($proveedor);

    }

    public function listar(): array
    {
        return $this->proveedorRepository->obtenerTodos();
    }

    public function buscar(int $idProveedor): ?Proveedor
    {
        return $this->proveedorRepository->obtenerPorId($idProveedor);
    }

    public function editar(Proveedor $proveedor): bool
    {
        return $this->proveedorRepository->actualizar($proveedor);
    }

    public function eliminar(int $idProveedor): bool
    {
        return $this->proveedorRepository->eliminar($idProveedor);
    }

}