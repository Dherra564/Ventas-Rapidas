<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Usuario.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/HistorialCampoRepository.php";

class UsuarioRepository
{
    use GeneradorId;

    private PDO $conexion;
    private HistorialCampoRepository $historialNombre;
    private HistorialCampoRepository $historialCorreo;
    private HistorialCampoRepository $historialPerfilImagen;
    private HistorialCampoRepository $historialPassword;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->historialNombre = new HistorialCampoRepository("tbusuarionombrecompletohistorico", "tbusuarionombrecompletohistoricoid", "tbusuarioid", $this->conexion);
        $this->historialCorreo = new HistorialCampoRepository("tbusuariocorreohistorico", "tbusuariocorreohistoricoid", "tbusuarioid", $this->conexion);
        $this->historialPerfilImagen = new HistorialCampoRepository("tbusuarioperfilimagenhistorico", "tbusuarioperfilimagenhistoricoid", "tbusuarioid", $this->conexion);
        $this->historialPassword = new HistorialCampoRepository("tbusuariopasswordhistorico", "tbusuariopasswordhistoricoid", "tbusuarioid", $this->conexion);
    }

    public function insertar(Usuario $usuario): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbusuario", "tbusuarioid");

        $sql = "INSERT INTO tbusuario
                (
                    tbusuarioid,
                    tbusuarioidentificacionnumero,
                    tbusuarionombrecompleto,
                    tbusuarioperfilimagen,
                    tbusuariocorreo,
                    tbusuariopassword,
                    tbusuarioactivo
                )
                VALUES
                (
                    :id,
                    :identificacion,
                    :nombre,
                    :perfilImagen,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":identificacion" => $usuario->getIdentificacion(),
            ":nombre" => $usuario->getNombreCompleto(),
            ":perfilImagen" => $usuario->getPerfilImagen(),
            ":correo" => $usuario->getCorreo(),
            ":password" => $usuario->getPasswordHash(),
            ":activo" => (int) $usuario->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorId(int $idUsuario): ?Usuario
    {
        return $this->buscarUno("tbusuarioid", $idUsuario);
    }

    public function obtenerPorCorreo(string $correo): ?Usuario
    {
        return $this->buscarUno("tbusuariocorreo", $correo);
    }

    public function obtenerPorIdentificacion(string $identificacion): ?Usuario
    {
        return $this->buscarUno("tbusuarioidentificacionnumero", $identificacion);
    }

    // $excluirIdUsuario sirve al editar un perfil: el usuario no cuenta contra sí mismo.
    public function existeCorreo(string $correo, ?int $excluirIdUsuario = null): bool
    {
        return $this->existe("tbusuariocorreo", $correo, $excluirIdUsuario);
    }

    public function existeIdentificacion(string $identificacion, ?int $excluirIdUsuario = null): bool
    {
        return $this->existe("tbusuarioidentificacionnumero", $identificacion, $excluirIdUsuario);
    }

    public function actualizar(Usuario $usuario): bool
    {
        $anterior = $this->obtenerPorId($usuario->getIdUsuario());

        $sql = "UPDATE tbusuario
                SET
                    tbusuarionombrecompleto = :nombre,
                    tbusuarioperfilimagen = :perfilImagen,
                    tbusuariocorreo = :correo,
                    tbusuariopassword = :password,
                    tbusuarioactivo = :activo
                WHERE tbusuarioid = :id";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":nombre" => $usuario->getNombreCompleto(),
            ":perfilImagen" => $usuario->getPerfilImagen(),
            ":correo" => $usuario->getCorreo(),
            ":password" => $usuario->getPasswordHash(),
            ":activo" => (int) $usuario->isActivo(),
            ":id" => $usuario->getIdUsuario()
        ]);

        if ($exito && $anterior !== null) {
            $id = $usuario->getIdUsuario();
            $this->historialNombre->registrarSiCambio($id, $anterior->getNombreCompleto(), $usuario->getNombreCompleto());
            $this->historialCorreo->registrarSiCambio($id, $anterior->getCorreo(), $usuario->getCorreo());
            $this->historialPerfilImagen->registrarSiCambio($id, $anterior->getPerfilImagen(), $usuario->getPerfilImagen());
        }

        return $exito;
    }

    public function actualizarPasswordHash(int $idUsuario, string $passwordHash): bool
    {
        $anterior = $this->obtenerPorId($idUsuario);

        $sql = "UPDATE tbusuario SET tbusuariopassword = :password WHERE tbusuarioid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":password" => $passwordHash, ":id" => $idUsuario]);

        if ($exito && $anterior !== null) {
            $this->historialPassword->registrar($idUsuario, $anterior->getPasswordHash(), $passwordHash);
        }

        return $exito;
    }

    public function actualizarPerfilImagen(int $idUsuario, ?string $perfilImagen): bool
    {
        $anterior = $this->obtenerPorId($idUsuario);

        $sql = "UPDATE tbusuario SET tbusuarioperfilimagen = :perfilImagen WHERE tbusuarioid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":perfilImagen" => $perfilImagen, ":id" => $idUsuario]);

        if ($exito && $anterior !== null) {
            $this->historialPerfilImagen->registrarSiCambio($idUsuario, $anterior->getPerfilImagen(), $perfilImagen);
        }

        return $exito;
    }

    public function obtenerUltimosHashesPassword(int $idUsuario, int $cantidad = 2): array
    {
        return $this->historialPassword->obtenerUltimosValores($idUsuario, $cantidad);
    }

    // Las columnas que llegan aquí son siempre constantes internas, nunca datos del usuario.
    private function buscarUno(string $columna, $valor): ?Usuario
    {
        $sql = "SELECT * FROM tbusuario WHERE $columna = :valor";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":valor" => $valor]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    private function existe(string $columna, string $valor, ?int $excluirIdUsuario): bool
    {
        $sql = "SELECT COUNT(*) FROM tbusuario WHERE $columna = :valor";
        $parametros = [":valor" => $valor];

        if ($excluirIdUsuario !== null) {
            $sql .= " AND tbusuarioid <> :excluir";
            $parametros[":excluir"] = $excluirIdUsuario;
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function mapearFila(array $fila): Usuario
    {
        return new Usuario(
            $fila["tbusuarionombrecompleto"],
            $fila["tbusuarioidentificacionnumero"],
            $fila["tbusuariocorreo"],
            $fila["tbusuariopassword"],
            $fila["tbusuarioperfilimagen"],
            (bool) $fila["tbusuarioactivo"],
            (int) $fila["tbusuarioid"],
            $fila["tbusuarioregistrofecha"] !== null ? new DateTime($fila["tbusuarioregistrofecha"]) : null
        );
    }
}