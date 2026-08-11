<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/ProductoLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";

class ProductoLocalRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function insertar(ProductoLocal $productoLocal): int|false
    {
        $this->validarReferencia(
            $this->conexion,
            "tbproducto",
            "tbproductoid",
            $productoLocal->getIdProducto(),
            "El producto con ID {$productoLocal->getIdProducto()} no existe"
        );

        $this->validarReferencia(
            $this->conexion,
            "tblocal",
            "tblocalid",
            $productoLocal->getIdLocal(),
            "El local con ID {$productoLocal->getIdLocal()} no existe"
        );

        $id = $this->generarSiguienteId($this->conexion, "tbproductolocal", "tbproductolocalid");

        $sql = "INSERT INTO tbproductolocal
                (tbproductolocalid, tbproductoid, tblocalid, tbproductolocalactivo)
                VALUES (:id, :idProducto, :idLocal, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idProducto" => $productoLocal->getIdProducto(),
            ":idLocal" => $productoLocal->getIdLocal(),
            ":activo" => $productoLocal->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerLocalesPorProducto(int $idProducto): array
    {
        $sql = "SELECT tblocalid
                FROM tbproductolocal
                WHERE tbproductoid = :idProducto
                AND tbproductolocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idProducto" => $idProducto]);

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerProductosPorLocal(int $idLocal): array
    {
        $sql = "SELECT tbproductoid
                FROM tbproductolocal
                WHERE tblocalid = :idLocal
                AND tbproductolocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    public function eliminar(int $idProductoLocal): bool
    {
        $sql = "UPDATE tbproductolocal SET tbproductolocalactivo = 0 WHERE tbproductolocalid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idProductoLocal]);
    }
}