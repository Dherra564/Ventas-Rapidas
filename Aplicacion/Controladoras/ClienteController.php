<?php

require_once __DIR__ . "/../Repositorios/ClienteRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialPasswordRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialFotoPerfilRepository.php";
require_once __DIR__ . "/../Repositorios/UbicacionRepository.php";
require_once __DIR__ . "/../Repositorios/HistorialActividadSesionLocalRepository.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Modelos/HistorialPassword.php";
require_once __DIR__ . "/../Modelos/HistorialFotoPerfil.php";
require_once __DIR__ . "/../Comun/ValidadorPassword.php";
require_once __DIR__ . "/../Comun/ManejadorImagenes.php";
require_once __DIR__ . "/../Comun/ValidadorIdentificacion.php";

class ClienteController
{
    use ValidadorPassword, ManejadorImagenes, ValidadorIdentificacion;

    private ClienteRepository $clienteRepository;
    private HistorialPasswordRepository $historialPasswordRepository;
    private HistorialFotoPerfilRepository $historialFotoPerfilRepository;
    private UbicacionRepository $ubicacionRepository;
    private HistorialActividadSesionLocalRepository $historialActividadRepository;

    public function __construct()
    {
        $this->clienteRepository = new ClienteRepository();
        $this->historialPasswordRepository = new HistorialPasswordRepository();
        $this->historialFotoPerfilRepository = new HistorialFotoPerfilRepository();
        $this->ubicacionRepository = new UbicacionRepository();
        $this->historialActividadRepository = new HistorialActividadSesionLocalRepository();
    }

    public function registrar(
        string $nombreCompleto,
        string $tipoIdentificacion,
        string $numeroIdentificacion,
        string $correo,
        string $password,
        ?string $perfilImagen = null,
        ?int $idProvincia = null,
        ?int $idCanton = null,
        ?int $idDistrito = null,
        ?string $direccionExacta = null,
        ?string $referencia = null
    ): int|false {
        $this->validarIdentificacion($tipoIdentificacion, $numeroIdentificacion);
        $this->validarFormatoPassword($password);

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $cliente = new Cliente($nombreCompleto, $numeroIdentificacion, $correo, $passwordHash, $perfilImagen);

        if ($idProvincia !== null && $idCanton !== null && $idDistrito !== null && $direccionExacta !== null) {
            $ubicacion = new Ubicacion(
                null,
                $idProvincia,
                $idCanton,
                $idDistrito,
                $direccionExacta,
                $referencia,
                null
            );
            return $this->clienteRepository->insertarConUbicacion($cliente, $ubicacion);
        }

        return $this->clienteRepository->insertar($cliente);
    }

    public function login(string $correo, string $password): Cliente
    {
        $cliente = $this->clienteRepository->obtenerPorCorreo($correo);

        if ($cliente === null || !password_verify($password, $cliente->getPasswordHash())) {
            throw new InvalidArgumentException("Correo o contraseña incorrectos");
        }

        if (!$cliente->isActivo()) {
            throw new InvalidArgumentException("Esta cuenta de cliente está desactivada");
        }

        try {
            $this->historialActividadRepository->registrarLogin($cliente->getIdCliente(), 'Cliente');
        } catch (Exception $e) {
        }

        return $cliente;
    }

    public function actualizarUbicacionGPS(int $idCliente, float $latitud, float $longitud): bool
    {
        return $this->ubicacionRepository->actualizarCoordenadasCliente($idCliente, $latitud, $longitud);
    }

    public function cambiarPassword(int $idCliente, string $passwordActual, string $passwordNueva): bool
    {
        $cliente = $this->clienteRepository->obtenerPorId($idCliente);
        if ($cliente === null) {
            throw new InvalidArgumentException("El cliente con ID {$idCliente} no existe");
        }

        if (!password_verify($passwordActual, $cliente->getPasswordHash())) {
            $this->historialPasswordRepository->registrar(
                new HistorialPassword($idCliente, HistorialPassword::TIPO_CLIENTE, false)
            );
            throw new InvalidArgumentException("La contraseña actual no es correcta");
        }

        $this->validarFormatoPassword($passwordNueva);
        $this->validarPasswordDistinta($passwordNueva, $cliente->getPasswordHash());

        $nuevoHash = password_hash($passwordNueva, PASSWORD_DEFAULT);
        $exito = $this->clienteRepository->actualizarPasswordHash($idCliente, $nuevoHash);

        $this->historialPasswordRepository->registrar(
            new HistorialPassword($idCliente, HistorialPassword::TIPO_CLIENTE, $exito)
        );

        return $exito;
    }

    public function cambiarFotoPerfil(int $idCliente, ?array $archivo): string|false
    {
        $cliente = $this->clienteRepository->obtenerPorId($idCliente);
        if ($cliente === null) {
            throw new InvalidArgumentException("El cliente con ID {$idCliente} no existe");
        }

        $rutaAnterior = $cliente->getPerfilImagen();
        $rutaNueva = $this->subirImagenPerfil($archivo, "cliente_{$idCliente}");
        if ($rutaNueva === false) {
            return false;
        }

        $exito = $this->clienteRepository->actualizarPerfilImagen($idCliente, $rutaNueva);
        if ($exito) {
            $this->historialFotoPerfilRepository->registrar(
                new HistorialFotoPerfil($idCliente, HistorialFotoPerfil::TIPO_CLIENTE, $rutaNueva, $rutaAnterior)
            );
        }

        return $exito ? $rutaNueva : false;
    }

    public function listar(): array { return $this->clienteRepository->obtenerTodos(); }
    public function buscar(int $idCliente): ?Cliente { return $this->clienteRepository->obtenerPorId($idCliente); }
    public function buscarConUbicacion(int $idCliente): ?array { return $this->clienteRepository->obtenerClienteConUbicacion($idCliente); }
    public function editar(Cliente $cliente): bool { return $this->clienteRepository->actualizar($cliente); }
    public function activar(int $idCliente): bool { return $this->clienteRepository->activar($idCliente); }
    public function eliminar(int $idCliente): bool { return $this->clienteRepository->eliminar($idCliente); }

    public function buscarConFiltros(?string $nombre = null, ?bool $activo = null): array
    {
        return $this->clienteRepository->buscar($nombre, $activo);
    }

    public function existeIdentificacion(string $identificacion): bool { return $this->clienteRepository->existeIdentificacion($identificacion); }
    public function existeCorreo(string $correo): bool { return $this->clienteRepository->existeCorreo($correo); }
    public function buscarPorIdentificacion(string $identificacion): ?Cliente { return $this->clienteRepository->obtenerPorIdentificacion($identificacion); }
}
