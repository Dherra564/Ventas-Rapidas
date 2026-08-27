<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/HistorialUbicacion.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class HistorialUbicacionRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(HistorialUbicacion $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbhistorialubicacion", "tbhistorialubicacionid");

        $sql = "INSERT INTO tbhistorialubicacion
                (
                    tbhistorialubicacionid,
                    tbubicacionid,
                    tbhistorialubicacionusuarioid,
                    tbhistorialubicacionusuariotipo,
                    tbhistorialubicacioncampo,
                    tbhistorialubicacionvaloranterior,
                    tbhistorialubicacionvalornuevo,
                    tbhistorialubicacionactivo
                )
                VALUES
                (
                    :id,
                    :idUbicacion,
                    :idUsuario,
                    :tipoUsuario,
                    :campo,
                    :valorAnterior,
                    :valorNuevo,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUbicacion" => $historial->getIdUbicacion(),
            ":idUsuario" => $historial->getIdUsuario(),
            ":tipoUsuario" => $historial->getTipoUsuario(),
            ":campo" => $historial->getCampo(),
            ":valorAnterior" => $historial->getValorAnterior(),
            ":valorNuevo" => $historial->getValorNuevo(),
            ":activo" => $historial->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerUltimoPorUsuario(int $idUsuario, string $tipoUsuario): ?HistorialUbicacion
    {
        $sql = "SELECT * FROM tbhistorialubicacion
                WHERE tbhistorialubicacionusuarioid = :idUsuario
                  AND tbhistorialubicacionusuariotipo = :tipoUsuario
                ORDER BY tbhistorialubicacionfecha DESC
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        $sql = "SELECT * FROM tbhistorialubicacion
                WHERE tbhistorialubicacionusuarioid = :idUsuario
                  AND tbhistorialubicacionusuariotipo = :tipoUsuario
                ORDER BY tbhistorialubicacionfecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        $registros = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): HistorialUbicacion
    {
        return new HistorialUbicacion(
            $fila["tbubicacionid"] !== null ? (int) $fila["tbubicacionid"] : null,
            (int) $fila["tbhistorialubicacionusuarioid"],
            $fila["tbhistorialubicacionusuariotipo"],
            $fila["tbhistorialubicacioncampo"],
            $fila["tbhistorialubicacionvaloranterior"],
            $fila["tbhistorialubicacionvalornuevo"],
            (bool) $fila["tbhistorialubicacionactivo"],
            (int) $fila["tbhistorialubicacionid"],
            new DateTime($fila["tbhistorialubicacionfecha"])
        );
    }
}