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
                    tbcomeriantenombre,
                    tbcomerciantealias,
                    tbcomerciantecedula,
                    tbcomerciantecorreo,
                    tbcomeriantepassword,
                    tbcomercianteactivo
                )
                VALUES
                (
                    :id,
                    :nombre,
                    :alias,
                    :cedula,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":cedula" => $comerciante->getCedula(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcomerciante ORDER BY tbcomeriantenombre";

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

    /**
     * Busca comerciantes combinando filtros opcionales.
     * Todos los parámetros son opcionales; los que se pasen como null se ignoran.
     *
     * @param string|null $nombre  Coincidencia parcial (LIKE) sobre el nombre completo
     * @param string|null $alias   Coincidencia parcial (LIKE) sobre el alias
     * @param bool|null   $activo  Coincidencia exacta sobre el estado activo
     */
    public function buscar(?string $nombre = null, ?string $alias = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "tbcomeriantenombre LIKE :nombre";
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

        $sql .= " ORDER BY tbcomeriantenombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $this->mapearFilas($consulta);
    }

    public function actualizar(Comerciante $comerciante): bool
    {
        // La cédula no se actualiza intencionalmente: es un identificador fijo del comerciante.
        $sql = "UPDATE tbcomerciante
                SET
                    tbcomeriantenombre = :nombre,
                    tbcomerciantealias = :alias,
                    tbcomerciantecorreo = :correo,
                    tbcomeriantepassword = :password,
                    tbcomercianteactivo = :activo
                WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
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

    /**
     * Mapea todas las filas de un PDOStatement a objetos Comerciante.
     */
    private function mapearFilas(PDOStatement $consulta): array
    {
        $comerciantes = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $comerciantes[] = $this->mapearFila($fila);
        }

        return $comerciantes;
    }

    /**
     * Mapea una sola fila a un objeto Comerciante.
     */
    private function mapearFila(array $fila): Comerciante
    {
        return new Comerciante(
            $fila["tbcomeriantenombre"],
            $fila["tbcomerciantealias"],
            $fila["tbcomerciantecedula"],
            $fila["tbcomerciantecorreo"],
            $fila["tbcomeriantepassword"],
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomeriantefecharegistroportal"] != null
            ? new DateTime($fila["tbcomeriantefecharegistroportal"])
            : null
        );
    }
}