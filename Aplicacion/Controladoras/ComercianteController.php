<?php

require_once __DIR__ . "/../Repositorios/ComercianteRepository.php";
require_once __DIR__ . "/../Repositorios/PasswordHistorialRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialFotoPerfilRepository.php";
require_once __DIR__ . "/../Repositorios/SesionActicoHistoricoRepository.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Modelos/PasswordHistorial.php";
require_once __DIR__ . "/../Modelos/HistorialFotoPerfil.php";
require_once __DIR__ . "/../Comun/ValidadorPassword.php";
require_once __DIR__ . "/../Comun/ManejadorImagenes.php";
require_once __DIR__ . "/../Comun/ValidadorIdentificacion.php";

class ComercianteController
{
    use ValidadorPassword, ManejadorImagenes, ValidadorIdentificacion;

    private ComercianteRepository $comercianteRepository;
    private PasswordHistorialRepository $historialPasswordRepository;
    private HistorialFotoPerfilRepository $historialFotoPerfilRepository;
    private SesionActicoHistoricoRepository $historialActividadRepository;

    public function __construct()
    {
        $this->comercianteRepository = new ComercianteRepository();
        $this->historialPasswordRepository = new PasswordHistorialRepository();
        $this->historialFotoPerfilRepository = new HistorialFotoPerfilRepository();
        $this->historialActividadRepository = new SesionActicoHistoricoRepository();
    }


    public function registrar(
        string $nombre,
        string $alias,
        string $tipoIdentificacion,
        string $numeroIdentificacion,
        string $correo,
        string $password,
        ?string $perfilImagen = null
    ): int|false {
        $this->validarIdentificacion($tipoIdentificacion, $numeroIdentificacion);
        $this->validarFormatoPassword($password);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $comerciante = new Comerciante(
            $nombre,
            $alias,
            $numeroIdentificacion,
            $correo,
            $passwordHash,
            $perfilImagen
        );

        return $this->comercianteRepository->insertar($comerciante);
    }

    public function login(string $correo, string $password): Comerciante
    {
        $comerciante = $this->comercianteRepository->obtenerPorCorreo($correo);

        if ($comerciante === null || !password_verify($password, $comerciante->getPasswordHash())) {
            throw new InvalidArgumentException("Correo o contraseña incorrectos");
        }

        if (!$comerciante->isActivo()) {
            throw new InvalidArgumentException("Esta cuenta de comerciante está desactivada");
        }

        try {
            $this->historialActividadRepository->registrarLogin($comerciante->getIdComerciante(), 'Comerciante');
        } catch (Exception $e) {
        }

        return $comerciante;
    }

    public function cambiarPassword(int $idComerciante, string $passwordActual, string $passwordNueva): bool
    {
        $comerciante = $this->comercianteRepository->obtenerPorId($idComerciante);
        if ($comerciante === null) {
            throw new InvalidArgumentException("El comerciante con ID {$idComerciante} no existe");
        }

        if (!password_verify($passwordActual, $comerciante->getPasswordHash())) {
            throw new InvalidArgumentException("La contraseña actual no es correcta");
        }

        $this->validarFormatoPassword($passwordNueva);
        $this->validarPasswordDistinta($passwordNueva, $comerciante->getPasswordHash());

        $hashesRecientes = $this->historialPasswordRepository->obtenerUltimosHashes(
            $idComerciante,
            PasswordHistorial::TIPO_COMERCIANTE,
            2
        );
        $this->validarPasswordNoReciente($passwordNueva, $hashesRecientes);

        $hashAnterior = $comerciante->getPasswordHash();
        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $exito = $this->comercianteRepository->actualizarPasswordHash($idComerciante, $nuevoHash);

        if ($exito) {
            $this->historialPasswordRepository->registrar(
                new PasswordHistorial($idComerciante, PasswordHistorial::TIPO_COMERCIANTE, $hashAnterior, $nuevoHash)
            );
        }

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

    public function listar(): array { return $this->comercianteRepository->obtenerTodos(); }
    public function buscar(int $idComerciante): ?Comerciante { return $this->comercianteRepository->obtenerPorId($idComerciante); }
    public function editar(Comerciante $comerciante): bool { return $this->comercianteRepository->actualizar($comerciante); }
    public function activar(int $idComerciante): bool { return $this->comercianteRepository->activar($idComerciante); }
    public function eliminar(int $idComerciante): bool { return $this->comercianteRepository->eliminar($idComerciante); }

    public function buscarConFiltros(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        return $this->comercianteRepository->buscar($nombre, $alias, $activo);
    }

    public function existeCedula(string $cedula): bool { return $this->comercianteRepository->existeCedula($cedula); }
    public function existeIdentificacion(string $identificacion): bool { return $this->existeCedula($identificacion); }
    public function existeCorreo(string $correo): bool { return $this->comercianteRepository->existeCorreo($correo); }
    public function buscarPorCedula(string $cedula): ?Comerciante { return $this->comercianteRepository->obtenerPorCedula($cedula); }
    public function buscarPorIdentificacion(string $identificacion): ?Comerciante { return $this->buscarPorCedula($identificacion); }
}