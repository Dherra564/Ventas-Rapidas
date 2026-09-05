<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/HistorialCampoRepository.php";

class ComercianteRepository
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
        $this->historialNombre = new HistorialCampoRepository("tbcomerciantenombrehistorico", "tbcomerciantenombrehistoricoid", "tbcomercianteid", $this->conexion);
        $this->historialCorreo = new HistorialCampoRepository("tbcomerciantecorreohistorico", "tbcomerciantecorreohistoricoid", "tbcomercianteid", $this->conexion);
        $this->historialPerfilImagen = new HistorialCampoRepository("tbcomercianteperfilimagenhistorico", "tbcomercianteperfilimagenhistoricoid", "tbcomercianteid", $this->conexion);
        $this->historialPassword = new HistorialCampoRepository("tbcomerciantepasswordhistorico", "tbcomerciantepasswordhistoricoid", "tbcomercianteid", $this->conexion);
    }

    public function insertar(Comerciante $comerciante): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbcomerciante", "tbcomercianteid");

        $sql = "INSERT INTO tbcomerciante
                (
                    tbcomercianteid,
                    tbcomercianteidentificacionnumero,
                    tbcomerciantenombre,
                    tbcomerciantealias,
                    tbcomercianteperfilimagen,
                    tbcomerciantecorreo,
                    tbcomerciantepassword,
                    tbcomercianteactivo
                )
                VALUES
                (
                    :id,
                    :identificacion,
                    :nombre,
                    :alias,
                    :perfilImagen,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":identificacion" => $comerciante->getCedula(),
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":perfilImagen" => $comerciante->getPerfilImagen(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcomerciante ORDER BY tbcomerciantenombre";
        $consulta = $this->conexion->query($sql);
        return $this->mapearFilas($consulta);
    }

    public function obtenerPorId(int $idComerciante): ?Comerciante
    {
        $sql = "SELECT * FROM tbcomerciante WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idComerciante]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function buscar(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "tbcomerciantenombre LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($alias !== null && $alias !== "") {
            $condiciones[] = "tbcomerciantealias LIKE :alias";
            $parametros[":alias"] = "%{$alias}%";
        }

        if ($activo !== null) {
            $condiciones[] = "tbcomercianteactivo = :activo";
            $parametros[":activo"] = $activo;
        }

        $sql = "SELECT * FROM tbcomerciante";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY tbcomerciantenombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $this->mapearFilas($consulta);
    }

    public function actualizar(Comerciante $comerciante): bool
    {
        $anterior = $this->obtenerPorId($comerciante->getIdComerciante());

        $sql = "UPDATE tbcomerciante
            SET
                tbcomerciantenombre = :nombre,
                tbcomerciantealias = :alias,
                tbcomercianteperfilimagen = :perfilImagen,
                tbcomerciantecorreo = :correo,
                tbcomerciantepassword = :password,
                tbcomercianteactivo = :activo
            WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":perfilImagen" => $comerciante->getPerfilImagen(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo(),
            ":id" => $comerciante->getIdComerciante()
        ]);

        if ($exito && $anterior !== null) {
            $id = $comerciante->getIdComerciante();
            $this->historialNombre->registrarSiCambio($id, $anterior->getNombreCompleto(), $comerciante->getNombreCompleto());
            $this->historialCorreo->registrarSiCambio($id, $anterior->getCorreo(), $comerciante->getCorreo());
            $this->historialPerfilImagen->registrarSiCambio($id, $anterior->getPerfilImagen(), $comerciante->getPerfilImagen());
        }

        return $exito;
    }

    public function actualizarPasswordHash(int $idComerciante, string $passwordHash): bool
    {
        $anterior = $this->obtenerPorId($idComerciante);

        $sql = "UPDATE tbcomerciante SET tbcomerciantepassword = :password WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":password" => $passwordHash, ":id" => $idComerciante]);

        if ($exito && $anterior !== null) {
            $this->historialPassword->registrar($idComerciante, $anterior->getPasswordHash(), $passwordHash);
        }

        return $exito;
    }

    public function actualizarPerfilImagen(int $idComerciante, ?string $perfilImagen): bool
    {
        $anterior = $this->obtenerPorId($idComerciante);

        $sql = "UPDATE tbcomerciante SET tbcomercianteperfilimagen = :perfilImagen WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":perfilImagen" => $perfilImagen, ":id" => $idComerciante]);

        if ($exito && $anterior !== null) {
            $this->historialPerfilImagen->registrarSiCambio($idComerciante, $anterior->getPerfilImagen(), $perfilImagen);
        }

        return $exito;
    }

    public function obtenerUltimosHashesPassword(int $idComerciante, int $cantidad = 2): array
    {
        return $this->historialPassword->obtenerUltimosValores($idComerciante, $cantidad);
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

    public function existeCedula(string $cedula): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcomerciante WHERE tbcomercianteidentificacionnumero = :cedula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":cedula" => $cedula]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcomerciante WHERE tbcomerciantecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function obtenerPorCedula(string $cedula): ?Comerciante
    {
        $sql = "SELECT * FROM tbcomerciante WHERE tbcomercianteidentificacionnumero = :cedula";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":cedula" => $cedula]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorCorreo(string $correo): ?Comerciante
    {
        $sql = "SELECT * FROM tbcomerciante WHERE tbcomerciantecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
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
            $fila["tbcomerciantenombre"],
            $fila["tbcomerciantealias"],
            $fila["tbcomercianteidentificacionnumero"],
            $fila["tbcomerciantecorreo"],
            $fila["tbcomerciantepassword"],
            $fila["tbcomercianteperfilimagen"],
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomercianteregistrofecha"] != null
            ? new DateTime($fila["tbcomercianteregistrofecha"])
            : null
        );
    }
}