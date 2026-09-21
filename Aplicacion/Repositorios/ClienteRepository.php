<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Modelos/Usuario.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/UbicacionRepository.php";
require_once __DIR__ . "/UsuarioRepository.php";

class ClienteRepository
{
    use GeneradorId;

    // Columnas del cliente + las de su usuario (mapearFila arma ambos objetos con esto).
    private const SELECT_BASE = "SELECT
                c.tbclienteid,
                c.tbusuarioid,
                c.tbclienteactivo,
                u.tbusuarionombrecompleto,
                u.tbusuarioidentificacionnumero,
                u.tbusuariocorreo,
                u.tbusuariopassword,
                u.tbusuarioperfilimagen,
                u.tbusuarioregistrofecha,
                u.tbusuarioactivo
            FROM tbcliente c
            INNER JOIN tbusuario u ON u.tbusuarioid = c.tbusuarioid";

    private PDO $conexion;
    private UbicacionRepository $ubicacionRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->ubicacionRepository = new UbicacionRepository($this->conexion);
        $this->usuarioRepository = new UsuarioRepository($this->conexion);
    }

    public function insertarConUbicacion(Cliente $cliente, Ubicacion $ubicacion): int|false
    {
        try {
            $this->conexion->beginTransaction();

            $idCliente = $this->insertar($cliente);
            if ($idCliente === false) {
                throw new Exception("No se pudo registrar el cliente");
            }

            $ubicacion->setIdCliente($idCliente);
            $ubicacion->setIdLocal(null);

            if (!$ubicacion->tieneDuenoValido()) {
                throw new InvalidArgumentException("La ubicación del cliente no quedó asociada correctamente");
            }

            $idUbicacion = $this->ubicacionRepository->insertar($ubicacion);
            if ($idUbicacion === false) {
                throw new Exception("No se pudo registrar la ubicación del cliente");
            }

            $this->conexion->commit();
            return $idCliente;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al insertar cliente: " . $e->getMessage());
            return false;
        }
    }

    // Si el cliente trae idUsuario = 0 es una persona nueva: se crea primero su usuario
    // (con el Usuario que lleva dentro) y luego su perfil de cliente, en una sola transacción.
    public function insertar(Cliente $cliente): int|false
    {
        $propiaTransaccion = !$this->conexion->inTransaction();

        try {
            if ($propiaTransaccion) {
                $this->conexion->beginTransaction();
            }

            $idUsuario = $cliente->getIdUsuario();

            if ($idUsuario === 0) {
                $usuario = $cliente->getUsuario();
                if ($usuario === null) {
                    throw new InvalidArgumentException("Faltan los datos de usuario del cliente");
                }

                $idUsuario = $this->usuarioRepository->insertar($usuario);
                if ($idUsuario === false) {
                    throw new Exception("No se pudo registrar el usuario");
                }
            }

            $id = $this->generarSiguienteId($this->conexion, "tbcliente", "tbclienteid");

            $sql = "INSERT INTO tbcliente
                    (
                        tbclienteid,
                        tbusuarioid,
                        tbclienteactivo
                    )
                    VALUES
                    (
                        :id,
                        :idUsuario,
                        :activo
                    )";

            $consulta = $this->conexion->prepare($sql);

            $exito = $consulta->execute([
                ":id" => $id,
                ":idUsuario" => $idUsuario,
                ":activo" => (int) $cliente->isActivo()
            ]);

            if (!$exito) {
                throw new Exception("No se pudo registrar el perfil de cliente");
            }

            if ($propiaTransaccion) {
                $this->conexion->commit();
            }

            return $id;
        } catch (Throwable $e) {
            if ($propiaTransaccion && $this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al insertar perfil de cliente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodos(): array
    {
        $sql = self::SELECT_BASE . " ORDER BY u.tbusuarionombrecompleto";
        $consulta = $this->conexion->query($sql);
        return $this->mapearFilas($consulta);
    }

    public function obtenerPorId(int $idCliente): ?Cliente
    {
        $sql = self::SELECT_BASE . " WHERE c.tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idCliente]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function buscar(?string $nombre = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "u.tbusuarionombrecompleto LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($activo !== null) {
            $condiciones[] = "c.tbclienteactivo = :activo";
            $parametros[":activo"] = (int) $activo;
        }

        $sql = self::SELECT_BASE;

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY u.tbusuarionombrecompleto";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $this->mapearFilas($consulta);
    }

    // Actualiza el estado del rol y, si el cliente trae sus datos de usuario cargados,
    // también los datos personales (con su historial) a través de UsuarioRepository.
    public function actualizar(Cliente $cliente): bool
    {
        try {
            $propiaTransaccion = !$this->conexion->inTransaction();

            if ($propiaTransaccion) {
                $this->conexion->beginTransaction();
            }

            $consulta = $this->conexion->prepare("UPDATE tbcliente SET tbclienteactivo = :activo WHERE tbclienteid = :id");
            $exito = $consulta->execute([
                ":activo" => (int) $cliente->isActivo(),
                ":id" => $cliente->getIdCliente()
            ]);

            $usuario = $cliente->getUsuario();
            if ($exito && $usuario !== null) {
                $exito = $this->usuarioRepository->actualizar($usuario);
            }

            if ($propiaTransaccion) {
                $exito ? $this->conexion->commit() : $this->conexion->rollBack();
            }

            return $exito;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al actualizar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPasswordHash(int $idCliente, string $passwordHash): bool
    {
        $idUsuario = $this->obtenerIdUsuario($idCliente);
        if ($idUsuario === null) {
            return false;
        }

        return $this->usuarioRepository->actualizarPasswordHash($idUsuario, $passwordHash);
    }

    public function actualizarPerfilImagen(int $idCliente, ?string $perfilImagen): bool
    {
        $idUsuario = $this->obtenerIdUsuario($idCliente);
        if ($idUsuario === null) {
            return false;
        }

        return $this->usuarioRepository->actualizarPerfilImagen($idUsuario, $perfilImagen);
    }

    public function obtenerUltimosHashesPassword(int $idCliente, int $cantidad = 2): array
    {
        $idUsuario = $this->obtenerIdUsuario($idCliente);
        if ($idUsuario === null) {
            return [];
        }

        return $this->usuarioRepository->obtenerUltimosHashesPassword($idUsuario, $cantidad);
    }

    public function activar(int $idCliente): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("UPDATE tbcliente SET tbclienteactivo = 1 WHERE tbclienteid = :id");
            $consulta->execute([":id" => $idCliente]);

            $consultaUbicacion = $this->conexion->prepare("UPDATE tbubicacion SET tbubicacionactivo = 1 WHERE tbclienteid = :id");
            $consultaUbicacion->execute([":id" => $idCliente]);

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    public function eliminar(int $idCliente): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("UPDATE tbcliente SET tbclienteactivo = 0 WHERE tbclienteid = :id");
            $consulta->execute([":id" => $idCliente]);

            $consultaUbicacion = $this->conexion->prepare("UPDATE tbubicacion SET tbubicacionactivo = 0 WHERE tbclienteid = :id");
            $consultaUbicacion->execute([":id" => $idCliente]);

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    // La unicidad se valida contra todos los usuarios (clientes y comerciantes).
    public function existeIdentificacion(string $identificacion): bool
    {
        return $this->usuarioRepository->existeIdentificacion($identificacion);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->usuarioRepository->existeCorreo($correo);
    }

    public function obtenerPorIdentificacion(string $identificacion): ?Cliente
    {
        $sql = self::SELECT_BASE . " WHERE u.tbusuarioidentificacionnumero = :identificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":identificacion" => $identificacion]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorCorreo(string $correo): ?Cliente
    {
        $sql = self::SELECT_BASE . " WHERE u.tbusuariocorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerClienteConUbicacion(int $idCliente): ?array
    {
        $cliente = $this->obtenerPorId($idCliente);
        if ($cliente === null) {
            return null;
        }

        $ubicacion = $this->ubicacionRepository->obtenerPorCliente($idCliente);
        if ($ubicacion === null) {
            return null;
        }

        return ["cliente" => $cliente, "ubicacion" => $ubicacion];
    }

    public function obtenerPorIdUsuario(int $idUsuario): ?Cliente
    {
        $sql = self::SELECT_BASE . " WHERE c.tbusuarioid = :idUsuario";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idUsuario" => $idUsuario]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function existePorUsuario(int $idUsuario): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM tbcliente WHERE tbusuarioid = :idUsuario");
        $consulta->execute([":idUsuario" => $idUsuario]);
        return (int) $consulta->fetchColumn() > 0;
    }

    private function obtenerIdUsuario(int $idCliente): ?int
    {
        $consulta = $this->conexion->prepare("SELECT tbusuarioid FROM tbcliente WHERE tbclienteid = :id");
        $consulta->execute([":id" => $idCliente]);
        $valor = $consulta->fetchColumn();
        return $valor === false ? null : (int) $valor;
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $clientes = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = $this->mapearFila($fila);
        }
        return $clientes;
    }

    private function mapearFila(array $fila): Cliente
    {
        return new Cliente(
            (int) $fila["tbusuarioid"],
            (bool) $fila["tbclienteactivo"],
            (int) $fila["tbclienteid"],
            $this->usuarioRepository->mapearFila($fila)
        );
    }
}
