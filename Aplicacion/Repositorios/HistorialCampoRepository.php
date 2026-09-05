<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/HistorialCampo.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

/**
 * Repositorio genérico para cualquier tabla de historial de un solo campo:
 * (id, id_entidad, valoranterior, valornuevo, fecha) — sin columna "activo".
 *
 * Uso: un objeto por tabla, por ejemplo:
 *   new HistorialCampoRepository("tblocalnombrehistorico", "tblocalnombrehistoricoid", "tblocalid");
 */
class HistorialCampoRepository
{
    use GeneradorId;

    private PDO $conexion;
    private string $tabla;
    private string $columnaId;
    private string $columnaEntidad;

    public function __construct(string $tabla, string $columnaId, string $columnaEntidad, ?PDO $conexion = null)
    {
        $this->tabla = $tabla;
        $this->columnaId = $columnaId;
        $this->columnaEntidad = $columnaEntidad;
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(int $idEntidad, $valorAnterior, $valorNuevo): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, $this->tabla, $this->columnaId);

        $sql = "INSERT INTO {$this->tabla}
                ({$this->columnaId}, {$this->columnaEntidad}, valoranterior, valornuevo, fecha)
                VALUES (:id, :idEntidad, :valorAnterior, :valorNuevo, NOW())";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idEntidad" => $idEntidad,
            ":valorAnterior" => $valorAnterior,
            ":valorNuevo" => $valorNuevo
        ]);

        return $exito ? $id : false;
    }

    // Solo registra si el valor realmente cambió — evita ruido en el historial.
    public function registrarSiCambio(int $idEntidad, $valorAnterior, $valorNuevo): int|false
    {
        if ($valorAnterior == $valorNuevo) {
            return false;
        }

        return $this->registrar($idEntidad, $valorAnterior, $valorNuevo);
    }

    public function obtenerPorEntidad(int $idEntidad): array
    {
        $sql = "SELECT * FROM {$this->tabla}
                WHERE {$this->columnaEntidad} = :idEntidad
                ORDER BY fecha DESC, {$this->columnaId} DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idEntidad" => $idEntidad]);

        return $this->mapearFilas($consulta);
    }

    // Los últimos $cantidad valores usados (útil para "no repetir contraseña").
    public function obtenerUltimosValores(int $idEntidad, int $cantidad = 2): array
    {
        $sql = "SELECT valornuevo FROM {$this->tabla}
                WHERE {$this->columnaEntidad} = :idEntidad
                ORDER BY fecha DESC, {$this->columnaId} DESC
                LIMIT :cantidad";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idEntidad", $idEntidad, PDO::PARAM_INT);
        $consulta->bindValue(":cantidad", $cantidad, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = new HistorialCampo(
                (int) $fila[$this->columnaEntidad],
                $fila["valoranterior"],
                $fila["valornuevo"],
                (int) $fila[$this->columnaId],
                new DateTime($fila["fecha"])
            );
        }

        return $registros;
    }
}