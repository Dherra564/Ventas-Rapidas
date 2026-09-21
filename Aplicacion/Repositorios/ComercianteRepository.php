<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Modelos/Usuario.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/UsuarioRepository.php";

class ComercianteRepository
{
    use GeneradorId;

    // Columnas del comerciante + las de su usuario (mapearFila arma ambos objetos con esto).
    private const SELECT_BASE = "SELECT
                m.tbcomercianteid,
                m.tbusuarioid,
                m.tbcomerciantealias,
                m.tbcomercianteregistrofecha,
                m.tbcomercianteactivo,
                u.tbusuarionombrecompleto,
                u.tbusuarioidentificacionnumero,
                u.tbusuariocorreo,
                u.tbusuariopassword,
                u.tbusuarioperfilimagen,
                u.tbusuarioregistrofecha,
                u.tbusuarioactivo
            FROM tbcomerciante m
            INNER JOIN tbusuario u ON u.tbusuarioid = m.tbusuarioid";

    private PDO $conexion;
    private UsuarioRepository $usuarioRepository;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->usuarioRepository = new UsuarioRepository($this->conexion);
    }

    // Nadie se registra directo como comerciante: el usuario ya existe (es cliente)
    // y aquí solo se le crea su perfil de comerciante.
    public function insertar(Comerciante $comerciante): int|false
    {
        try {
            $idUsuario = $comerciante->getIdUsuario();

            if ($idUsuario <= 0 || $this->usuarioRepository->obtenerPorId($idUsuario) === null) {
                throw new InvalidArgumentException("El comerciante debe pertenecer a un usuario existente");
            }

            if ($this->existePorUsuario($idUsuario)) {
                throw new InvalidArgumentException("Este usuario ya tiene perfil de comerciante");
            }

            $id = $this->generarSiguienteId($this->conexion, "tbcomerciante", "tbcomercianteid");

            $sql = "INSERT INTO tbcomerciante
                    (
                        tbcomercianteid,
                        tbusuarioid,
                        tbcomerciantealias,
                        tbcomercianteactivo
                    )
                    VALUES
                    (
                        :id,
                        :idUsuario,
                        :alias,
                        :activo
                    )";

            $consulta = $this->conexion->prepare($sql);

            $exito = $consulta->execute([
                ":id" => $id,
                ":idUsuario" => $idUsuario,
                ":alias" => $comerciante->getAlias(),
                ":activo" => (int) $comerciante->isActivo()
            ]);

            return $exito ? $id : false;
        } catch (Throwable $e) {
            error_log("Error al insertar perfil de comerciante: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodos(): array
    {
        $sql = self::SELECT_BASE . " ORDER BY u.tbusuarionombrecompleto";
        $consulta = $this->conexion->query($sql);
        return $this->mapearFilas($consulta);
    }

    public function obtenerPorId(int $idComerciante): ?Comerciante
    {
        $sql = self::SELECT_BASE . " WHERE m.tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idComerciante]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    // Para el login único: dado el usuario, ¿tiene perfil de comerciante?
    public function obtenerPorIdUsuario(int $idUsuario): ?Comerciante
    {
        $sql = self::SELECT_BASE . " WHERE m.tbusuarioid = :idUsuario";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idUsuario" => $idUsuario]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function existePorUsuario(int $idUsuario): bool
    {
        $consulta = $this->conexion->prepare("SELECT COUNT(*) FROM tbcomerciante WHERE tbusuarioid = :idUsuario");
        $consulta->execute([":idUsuario" => $idUsuario]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function buscar(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "u.tbusuarionombrecompleto LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($alias !== null && $alias !== "") {
            $condiciones[] = "m.tbcomerciantealias LIKE :alias";
            $parametros[":alias"] = "%{$alias}%";
        }

        if ($activo !== null) {
            $condiciones[] = "m.tbcomercianteactivo = :activo";
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

    // Actualiza alias y estado del rol y, si el comerciante trae sus datos de usuario cargados,
    // también los datos personales (con su historial) a través de UsuarioRepository.
    public function actualizar(Comerciante $comerciante): bool
    {
        try {
            $propiaTransaccion = !$this->conexion->inTransaction();

            if ($propiaTransaccion) {
                $this->conexion->beginTransaction();
            }

            $consulta = $this->conexion->prepare(
                "UPDATE tbcomerciante
                 SET tbcomerciantealias = :alias, tbcomercianteactivo = :activo
                 WHERE tbcomercianteid = :id"
            );

            $exito = $consulta->execute([
                ":alias" => $comerciante->getAlias(),
                ":activo" => (int) $comerciante->isActivo(),
                ":id" => $comerciante->getIdComerciante()
            ]);

            $usuario = $comerciante->getUsuario();
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
            error_log("Error al actualizar comerciante: " . $e->getMessage());
            return false;
        }
    }

    public function actualizarPasswordHash(int $idComerciante, string $passwordHash): bool
    {
        $idUsuario = $this->obtenerIdUsuario($idComerciante);
        if ($idUsuario === null) {
            return false;
        }

        return $this->usuarioRepository->actualizarPasswordHash($idUsuario, $passwordHash);
    }

    public function actualizarPerfilImagen(int $idComerciante, ?string $perfilImagen): bool
    {
        $idUsuario = $this->obtenerIdUsuario($idComerciante);
        if ($idUsuario === null) {
            return false;
        }

        return $this->usuarioRepository->actualizarPerfilImagen($idUsuario, $perfilImagen);
    }

    public function obtenerUltimosHashesPassword(int $idComerciante, int $cantidad = 2): array
    {
        $idUsuario = $this->obtenerIdUsuario($idComerciante);
        if ($idUsuario === null) {
            return [];
        }

        return $this->usuarioRepository->obtenerUltimosHashesPassword($idUsuario, $cantidad);
    }

    public function activar(int $idComerciante): bool
    {
        $sql = "UPDATE tbcomerciante SET tbcomercianteactivo = 1 WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":id" => $idComerciante]);
    }

    public function eliminar(int $idComerciante): bool
    {
        $sql = "UPDATE tbcomerciante SET tbcomercianteactivo = 0 WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":id" => $idComerciante]);
    }

    // La unicidad se valida contra todos los usuarios (clientes y comerciantes).
    public function existeCedula(string $cedula): bool
    {
        return $this->usuarioRepository->existeIdentificacion($cedula);
    }

    public function existeCorreo(string $correo): bool
    {
        return $this->usuarioRepository->existeCorreo($correo);
    }

    public function obtenerPorCedula(string $cedula): ?Comerciante
    {
        $sql = self::SELECT_BASE . " WHERE u.tbusuarioidentificacionnumero = :cedula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":cedula" => $cedula]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorCorreo(string $correo): ?Comerciante
    {
        $sql = self::SELECT_BASE . " WHERE u.tbusuariocorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    private function obtenerIdUsuario(int $idComerciante): ?int
    {
        $consulta = $this->conexion->prepare("SELECT tbusuarioid FROM tbcomerciante WHERE tbcomercianteid = :id");
        $consulta->execute([":id" => $idComerciante]);
        $valor = $consulta->fetchColumn();
        return $valor === false ? null : (int) $valor;
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $comerciantes = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $comerciantes[] = $this->mapearFila($fila);
        }
        return $comerciantes;
    }

    private function mapearFila(array $fila): Comerciante
    {
        return new Comerciante(
            (int) $fila["tbusuarioid"],
            $fila["tbcomerciantealias"],
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomercianteregistrofecha"] !== null
                ? new DateTime($fila["tbcomercianteregistrofecha"])
                : null,
            $this->usuarioRepository->mapearFila($fila)
        );
    }
}