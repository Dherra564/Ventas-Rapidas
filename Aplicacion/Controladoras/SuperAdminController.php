<?php

require_once __DIR__ . "/../Repositorios/SuperAdminRepository.php";
require_once __DIR__ . "/../Repositorios/SuperAdminPasswordHistoricoRepository.php";
require_once __DIR__ . "/../Modelos/SuperAdmin.php";
require_once __DIR__ . "/../Comun/ValidadorPassword.php";

class SuperAdminController
{
    use ValidadorPassword;

    private SuperAdminRepository $superAdminRepository;
    private SuperAdminPasswordHistoricoRepository $historialLoginRepository;

    public function __construct()
    {
        $this->superAdminRepository = new SuperAdminRepository();
        $this->historialLoginRepository = new SuperAdminPasswordHistoricoRepository();
    }

    public function registrar(string $correo, string $nombreCompleto, string $password): int|false
    {
        $this->validarFormatoPassword($password);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $superAdmin = new SuperAdmin($correo, $passwordHash, $nombreCompleto);

        return $this->superAdminRepository->insertar($superAdmin);
    }

    public function login(string $correo, string $password): SuperAdmin
    {
        $superAdmin = $this->superAdminRepository->obtenerPorCorreo($correo);

        if ($superAdmin !== null) {
            $intentosFallidos = $this->historialLoginRepository->contarIntentosFallidosRecientes($superAdmin->getIdSuperAdmin(), 1);
            if ($intentosFallidos >= 5) {
                throw new InvalidArgumentException("Demasiados intentos fallidos. Intenta de nuevo en una hora.");
            }
        }

        if ($superAdmin === null || !password_verify($password, $superAdmin->getPasswordHash())) {
            if ($superAdmin !== null) {
                $this->historialLoginRepository->registrarIntentoLogin($superAdmin->getIdSuperAdmin(), false);
            }
            throw new InvalidArgumentException("Correo o contraseña incorrectos");
        }

        if (!$superAdmin->isActivo()) {
            throw new InvalidArgumentException("Esta cuenta de super admin está desactivada");
        }

        $this->historialLoginRepository->registrarIntentoLogin($superAdmin->getIdSuperAdmin(), true);

        return $superAdmin;
    }

    public function cambiarCorreo(int $idSuperAdmin, string $correoNuevo): bool
    {
        return $this->superAdminRepository->actualizarCorreo($idSuperAdmin, $correoNuevo);
    }

    public function cambiarPassword(int $idSuperAdmin, string $passwordActual, string $passwordNueva): bool
    {
        $superAdmin = $this->superAdminRepository->obtenerPorId($idSuperAdmin);
        if ($superAdmin === null) {
            throw new InvalidArgumentException("El super admin con ID {$idSuperAdmin} no existe");
        }

        if (!password_verify($passwordActual, $superAdmin->getPasswordHash())) {
            throw new InvalidArgumentException("La contraseña actual no es correcta");
        }

        $this->validarFormatoPassword($passwordNueva);
        $this->validarPasswordDistinta($passwordNueva, $superAdmin->getPasswordHash());

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        return $this->superAdminRepository->actualizarPasswordHash($idSuperAdmin, $nuevoHash);
    }

    public function buscar(int $idSuperAdmin): ?SuperAdmin
    {
        return $this->superAdminRepository->obtenerPorId($idSuperAdmin);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->superAdminRepository->existeCorreo($correo);
    }
}