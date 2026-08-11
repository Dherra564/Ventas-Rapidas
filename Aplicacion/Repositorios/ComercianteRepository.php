<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ComercianteRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Comerciante $comerciante): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbcomerciante", "tbcomercianteid");

        $sql = "INSERT INTO tbcomerciante
                (
                    tbcomercianteid,
                    tbcomerciantenombre,
                    tbcomerciantealias,
                    tbcomerciantenumerodeidentificacion,
                    tbcomercianteimagenperfil,
                    tbcomerciantecorreo,
                    tbcomerciantepassword,
                    tbcomercianteactivo
                )
                VALUES
                (
                    :id,
                    :nombre,
                    :alias,
                    :numeroIdentificacion,
                    :fotoPerfil,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":numeroIdentificacion" => $comerciante->getNumeroIdentificacion(),
            ":fotoPerfil" => $comerciante->getFotoPerfil(),
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

        if (!$fila) {
            return null;
        }

        return $this->mapearFila($fila);
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
        $sql = "UPDATE tbcomerciante
                SET
                    tbcomeriantenombre = :nombre,
                    tbcomerciantealias = :alias,
                    tbcomercianteimagenperfil = :fotoPerfil,
                    tbcomerciantecorreo = :correo,
                    tbcomerciantepassword = :password,
                    tbcomercianteactivo = :activo
                WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":fotoPerfil" => $comerciante->getFotoPerfil(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo(),
            ":id" => $comerciante->getIdComerciante()
        ]);
    }

    public function eliminar(int $idComerciante): bool
    {
        $sql = "UPDATE tbcomerciante SET tbcomercianteactivo = 0 WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idComerciante]);
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
            $fila["tbcomerciantenumerodeidentificacion"],
            $fila["tbcomerciantecorreo"],
            $fila["tbcomerciantepassword"],
            $fila["tbcomercianteimagenperfil"] ?? '',
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomerciantefecharegistroportal"] != null
            ? new DateTime($fila["tbcomerciantefecharegistroportal"])
            : null
        );
    }

    public function existeIdentificacion(string $numeroIdentificacion): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcomerciante WHERE tbcomerciantenumerodeidentificacion = :numeroIdentificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":numeroIdentificacion" => $numeroIdentificacion]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcomerciante WHERE tbcomerciantecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function obtenerPorIdentificacion(string $numeroIdentificacion): ?Comerciante
    {
        $sql = "SELECT * FROM tbcomerciante WHERE tbcomerciantenumerodeidentificacion = :numeroIdentificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":numeroIdentificacion" => $numeroIdentificacion]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }
}