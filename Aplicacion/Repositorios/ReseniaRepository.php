<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Resenia.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ReseniaRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(Resenia $resenia): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbresenia", "tbreseniaid");

        $sql = "INSERT INTO tbresenia
                (
                    tbreseniaid,
                    tbreseniaidcliente,
                    tbreseniaidlocal,
                    tbreseniacomentario,
                    tbreseniapuntuacion,
                    tbreseniaactivo
                )
                VALUES
                (
                    :id,
                    :idCliente,
                    :idLocal,
                    :comentario,
                    :puntuacion,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idCliente" => $resenia->getIdCliente(),
            ":idLocal" => $resenia->getIdLocal(),
            ":comentario" => $resenia->getComentario(),
            ":puntuacion" => $resenia->getPuntuacion(),
            ":activo" => $resenia->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorId(int $idResenia): ?Resenia
    {
        $sql = "SELECT * FROM tbresenia WHERE tbreseniaid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idResenia]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    // Reseñas de un local, más reciente primero.
    public function obtenerPorLocal(int $idLocal): array
    {
        $sql = "SELECT * FROM tbresenia
                WHERE tbreseniaidlocal = :idLocal
                  AND tbreseniaactivo = 1
                ORDER BY tbreseniafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return $this->mapearFilas($consulta);
    }

    // Reseñas hechas por un cliente, más reciente primero.
    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM tbresenia
                WHERE tbreseniaidcliente = :idCliente
                  AND tbreseniaactivo = 1
                ORDER BY tbreseniafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $this->mapearFilas($consulta);
    }

    // Promedio de puntuación de un local, redondeado a 1 decimal. Null si no tiene reseñas.
    public function obtenerPromedioPorLocal(int $idLocal): ?float
    {
        $sql = "SELECT ROUND(AVG(tbreseniapuntuacion), 1) AS promedio
                FROM tbresenia
                WHERE tbreseniaidlocal = :idLocal
                  AND tbreseniaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $promedio = $consulta->fetchColumn();

        return $promedio !== null ? (float) $promedio : null;
    }

    // Cantidad total de reseñas activas de un local.
    public function contarPorLocal(int $idLocal): int
    {
        $sql = "SELECT COUNT(*) FROM tbresenia
                WHERE tbreseniaidlocal = :idLocal
                  AND tbreseniaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return (int) $consulta->fetchColumn();
    }

    // Verifica si un cliente ya reseñó un local (útil para permitir solo una reseña por cliente/local).
    public function existeResenia(int $idCliente, int $idLocal): bool
    {
        $sql = "SELECT COUNT(*) FROM tbresenia
                WHERE tbreseniaidcliente = :idCliente
                  AND tbreseniaidlocal = :idLocal
                  AND tbreseniaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idCliente" => $idCliente,
            ":idLocal" => $idLocal
        ]);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function actualizar(Resenia $resenia): bool
    {
        $sql = "UPDATE tbresenia
                SET
                    tbreseniacomentario = :comentario,
                    tbreseniapuntuacion = :puntuacion,
                    tbreseniaactivo = :activo
                WHERE tbreseniaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":comentario" => $resenia->getComentario(),
            ":puntuacion" => $resenia->getPuntuacion(),
            ":activo" => $resenia->isActivo(),
            ":id" => $resenia->getIdResenia()
        ]);
    }

    public function eliminar(int $idResenia): bool
    {
        $sql = "UPDATE tbresenia SET tbreseniaactivo = 0 WHERE tbreseniaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idResenia]);
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): Resenia
    {
        return new Resenia(
            (int) $fila["tbreseniaidcliente"],
            (int) $fila["tbreseniaidlocal"],
            $fila["tbreseniacomentario"],
            (int) $fila["tbreseniapuntuacion"],
            (bool) $fila["tbreseniaactivo"],
            (int) $fila["tbreseniaid"],
            new DateTime($fila["tbreseniafecha"])
        );
    }
}