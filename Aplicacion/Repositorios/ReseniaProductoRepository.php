<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/ReseniaProducto.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ReseniaProductoRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(ReseniaProducto $resenia): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbreseniaproducto", "tbreseniaproductoid");

        $sql = "INSERT INTO tbreseniaproducto
                (
                    tbreseniaproductoid,
                    tbreseniaproductoidcliente,
                    tbreseniaproductoidproducto,
                    tbreseniaproductocomentario,
                    tbreseniaproductopuntuacion,
                    tbreseniaproductoactivo
                )
                VALUES
                (
                    :id,
                    :idCliente,
                    :idProducto,
                    :comentario,
                    :puntuacion,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idCliente" => $resenia->getIdCliente(),
            ":idProducto" => $resenia->getIdProducto(),
            ":comentario" => $resenia->getComentario(),
            ":puntuacion" => $resenia->getPuntuacion(),
            ":activo" => $resenia->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorId(int $id): ?ReseniaProducto
    {
        $sql = "SELECT * FROM tbreseniaproducto WHERE tbreseniaproductoid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $id]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorProducto(int $idProducto): array
    {
        $sql = "SELECT * FROM tbreseniaproducto
                WHERE tbreseniaproductoidproducto = :idProducto
                  AND tbreseniaproductoactivo = 1
                ORDER BY tbreseniaproductofecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idProducto" => $idProducto]);

        return $this->mapearFilas($consulta);
    }

    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM tbreseniaproducto
                WHERE tbreseniaproductoidcliente = :idCliente
                  AND tbreseniaproductoactivo = 1
                ORDER BY tbreseniaproductofecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $this->mapearFilas($consulta);
    }

    public function obtenerPromedioPorProducto(int $idProducto): ?float
    {
        $sql = "SELECT ROUND(AVG(tbreseniaproductopuntuacion), 1) AS promedio
                FROM tbreseniaproducto
                WHERE tbreseniaproductoidproducto = :idProducto
                  AND tbreseniaproductoactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idProducto" => $idProducto]);

        $promedio = $consulta->fetchColumn();
        return $promedio !== null ? (float) $promedio : null;
    }

    public function contarPorProducto(int $idProducto): int
    {
        $sql = "SELECT COUNT(*) FROM tbreseniaproducto
                WHERE tbreseniaproductoidproducto = :idProducto
                  AND tbreseniaproductoactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idProducto" => $idProducto]);

        return (int) $consulta->fetchColumn();
    }

    public function existeResenia(int $idCliente, int $idProducto): bool
    {
        $sql = "SELECT COUNT(*) FROM tbreseniaproducto
                WHERE tbreseniaproductoidcliente = :idCliente
                  AND tbreseniaproductoidproducto = :idProducto
                  AND tbreseniaproductoactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idCliente" => $idCliente,
            ":idProducto" => $idProducto
        ]);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function actualizar(ReseniaProducto $resenia): bool
    {
        $sql = "UPDATE tbreseniaproducto
                SET
                    tbreseniaproductocomentario = :comentario,
                    tbreseniaproductopuntuacion = :puntuacion,
                    tbreseniaproductoactivo = :activo
                WHERE tbreseniaproductoid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":comentario" => $resenia->getComentario(),
            ":puntuacion" => $resenia->getPuntuacion(),
            ":activo" => $resenia->isActivo(),
            ":id" => $resenia->getIdReseniaProducto()
        ]);
    }

    public function eliminar(int $id): bool
    {
        $sql = "UPDATE tbreseniaproducto SET tbreseniaproductoactivo = 0 WHERE tbreseniaproductoid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":id" => $id]);
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }
        return $registros;
    }

    private function mapearFila(array $fila): ReseniaProducto
    {
        return new ReseniaProducto(
            (int) $fila["tbreseniaproductoidcliente"],
            (int) $fila["tbreseniaproductoidproducto"],
            $fila["tbreseniaproductocomentario"],
            (int) $fila["tbreseniaproductopuntuacion"],
            (bool) $fila["tbreseniaproductoactivo"],
            (int) $fila["tbreseniaproductoid"],
            $fila["tbreseniaproductofecha"] ? new DateTime($fila["tbreseniaproductofecha"]) : null
        );
    }
}