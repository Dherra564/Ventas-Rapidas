<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Resena.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ResenaRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(Resena $resena): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbresena", "tbresenaid");

        $sql = "INSERT INTO tbresena
                (
                    tbresenaid,
                    tbresenaidcliente,
                    tbresenaidlocal,
                    tbresenacomentario,
                    tbresenapuntuacion,
                    tbresenaactivo
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
            ":idCliente" => $resena->getIdCliente(),
            ":idLocal" => $resena->getIdLocal(),
            ":comentario" => $resena->getComentario(),
            ":puntuacion" => $resena->getPuntuacion(),
            ":activo" => $resena->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorId(int $idResena): ?Resena
    {
        $sql = "SELECT * FROM tbresena WHERE tbresenaid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idResena]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    // Reseñas de un local, más reciente primero.
    public function obtenerPorLocal(int $idLocal): array
    {
        $sql = "SELECT * FROM tbresena
                WHERE tbresenaidlocal = :idLocal
                  AND tbresenaactivo = 1
                ORDER BY tbresenafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return $this->mapearFilas($consulta);
    }

    // Reseñas hechas por un cliente, más reciente primero.
    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM tbresena
                WHERE tbresenaidcliente = :idCliente
                  AND tbresenaactivo = 1
                ORDER BY tbresenafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $this->mapearFilas($consulta);
    }

    // Promedio de puntuación de un local, redondeado a 1 decimal. Null si no tiene reseñas.
    public function obtenerPromedioPorLocal(int $idLocal): ?float
    {
        $sql = "SELECT ROUND(AVG(tbresenapuntuacion), 1) AS promedio
                FROM tbresena
                WHERE tbresenaidlocal = :idLocal
                  AND tbresenaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $promedio = $consulta->fetchColumn();

        return $promedio !== null ? (float) $promedio : null;
    }

    // Cantidad total de reseñas activas de un local.
    public function contarPorLocal(int $idLocal): int
    {
        $sql = "SELECT COUNT(*) FROM tbresena
                WHERE tbresenaidlocal = :idLocal
                  AND tbresenaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return (int) $consulta->fetchColumn();
    }

    // Verifica si un cliente ya reseñó un local (útil para permitir solo una reseña por cliente/local).
    public function existeResena(int $idCliente, int $idLocal): bool
    {
        $sql = "SELECT COUNT(*) FROM tbresena
                WHERE tbresenaidcliente = :idCliente
                  AND tbresenaidlocal = :idLocal
                  AND tbresenaactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idCliente" => $idCliente,
            ":idLocal" => $idLocal
        ]);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function actualizar(Resena $resena): bool
    {
        $sql = "UPDATE tbresena
                SET
                    tbresenacomentario = :comentario,
                    tbresenapuntuacion = :puntuacion,
                    tbresenaactivo = :activo
                WHERE tbresenaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":comentario" => $resena->getComentario(),
            ":puntuacion" => $resena->getPuntuacion(),
            ":activo" => $resena->isActivo(),
            ":id" => $resena->getIdResena()
        ]);
    }

    public function eliminar(int $idResena): bool
    {
        $sql = "UPDATE tbresena SET tbresenaactivo = 0 WHERE tbresenaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idResena]);
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): Resena
    {
        return new Resena(
            (int) $fila["tbresenaidcliente"],
            (int) $fila["tbresenaidlocal"],
            $fila["tbresenacomentario"],
            (int) $fila["tbresenapuntuacion"],
            (bool) $fila["tbresenaactivo"],
            (int) $fila["tbresenaid"],
            new DateTime($fila["tbresenafecha"])
        );
    }
}