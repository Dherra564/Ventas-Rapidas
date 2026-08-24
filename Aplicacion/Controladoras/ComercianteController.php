<?php

require_once __DIR__ . "/../Repositorios/ComercianteRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialPasswordRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialFotoPerfilRepository.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Modelos/HistorialPassword.php";
require_once __DIR__ . "/../Modelos/HistorialFotoPerfil.php";
require_once __DIR__ . "/../Comun/ValidadorPassword.php";
require_once __DIR__ . "/../Comun/ManejadorImagenes.php";
require_once __DIR__ . "/../Comun/ValidadorIdentificacion.php";

class ComercianteController
{
    use ValidadorPassword, ManejadorImagenes, ValidadorIdentificacion;

    private ComercianteRepository $comercianteRepository;
    private HistorialPasswordRepository $historialPasswordRepository;
    private HistorialFotoPerfilRepository $historialFotoPerfilRepository;

    public function __construct()
    {
        $this->comercianteRepository = new ComercianteRepository();
        $this->historialPasswordRepository = new HistorialPasswordRepository();
        $this->historialFotoPerfilRepository = new HistorialFotoPerfilRepository();
    }

    public function registrar(
        string $nombre,
        string $alias,
        string $tipoIdentificacion,
        string $numeroIdentificacion,
        string $correo,
        string $password
    ): int|false {

        $this->validarIdentificacion($tipoIdentificacion, $numeroIdentificacion);
        $this->validarFormatoPassword($password);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $comerciante = new Comerciante(
            $nombre,
            $alias,
            $numeroIdentificacion,
            $correo,
            $passwordHash
        );

        return $this->comercianteRepository->insertar($comerciante);
    }

    public function cambiarPassword(int $idComerciante, string $passwordActual, string $passwordNueva): bool
    {
        $comerciante = $this->comercianteRepository->obtenerPorId($idComerciante);

        if ($comerciante === null) {
            throw new InvalidArgumentException("El comerciante con ID {$idComerciante} no existe");
        }

        if (!password_verify($passwordActual, $comerciante->getPasswordHash())) {
            $this->historialPasswordRepository->registrar(
                new HistorialPassword($idComerciante, HistorialPassword::TIPO_COMERCIANTE, false)
            );
            throw new InvalidArgumentException("La contraseña actual no es correcta");
        }

        $this->validarFormatoPassword($passwordNueva);
        $this->validarPasswordDistinta($passwordNueva, $comerciante->getPasswordHash());

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $exito = $this->comercianteRepository->actualizarPasswordHash($idComerciante, $nuevoHash);

        $this->historialPasswordRepository->registrar(
            new HistorialPassword($idComerciante, HistorialPassword::TIPO_COMERCIANTE, $exito)
        );

        return $exito;
    }

    public function cambiarFotoPerfil(int $idComerciante, ?array $archivo): string|false
    {
        $comerciante = $this->comercianteRepository->obtenerPorId($idComerciante);

        if ($comerciante === null) {
            throw new InvalidArgumentException("El comerciante con ID {$idComerciante} no existe");
        }

        $rutaAnterior = $comerciante->getPerfilImagen();
        $rutaNueva = $this->subirImagenPerfil($archivo, "comerciante_{$idComerciante}");

        if ($rutaNueva === false) {
            return false;
        }

        $exito = $this->comercianteRepository->actualizarPerfilImagen($idComerciante, $rutaNueva);

        if ($exito) {
            $this->historialFotoPerfilRepository->registrar(
                new HistorialFotoPerfil($idComerciante, HistorialFotoPerfil::TIPO_COMERCIANTE, $rutaNueva, $rutaAnterior)
            );
        }

        return $exito ? $rutaNueva : false;
    }

    public function listar(): array
    {
        return $this->comercianteRepository->obtenerTodos();
    }

    public function buscar(int $idComerciante): ?Comerciante
    {
        return $this->comercianteRepository->obtenerPorId($idComerciante);
    }

    public function editar(Comerciante $comerciante): bool
    {
        return $this->comercianteRepository->actualizar($comerciante);
    }

    public function eliminar(int $idComerciante): bool
    {
        return $this->comercianteRepository->eliminar($idComerciante);
    }

    public function buscarConFiltros(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        return $this->comercianteRepository->buscar($nombre, $alias, $activo);
    }

    public function existeCedula(string $cedula): bool
    {
        return $this->comercianteRepository->existeCedula($cedula);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->comercianteRepository->existeCorreo($correo);
    }

    public function buscarPorCedula(string $cedula): ?Comerciante
    {
        return $this->comercianteRepository->obtenerPorCedula($cedula);
    }
}