<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/CarritoCompra.php";
require_once __DIR__ . "/../Modelos/CarritoCompraDetalle.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class CarritoCompraRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function buscarPorClienteYLocal(int $idCliente, int $idLocal): ?CarritoCompra
    {
        $sql = "SELECT * FROM tbcarritocompra
                WHERE tbclienteid = :idCliente
                  AND tblocalid = :idLocal
                  AND tbcarritocompraactivo = 1
                ORDER BY tbcarritocompraid
                LIMIT 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente, ":idLocal" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearConDetalles($fila) : null;
    }

    public function listarPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM tbcarritocompra
                WHERE tbclienteid = :idCliente
                  AND tbcarritocompraactivo = 1
                ORDER BY tbcarritocompraid DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        $carritosCompra = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $carritosCompra[] = $this->mapearConDetalles($fila);
        }
        return $carritosCompra;
    }

    public function obtenerOCrear(int $idCliente, int $idLocal): CarritoCompra
    {
        $carritoCompra = $this->buscarPorClienteYLocal($idCliente, $idLocal);
        if ($carritoCompra !== null) {
            return $carritoCompra;
        }

        $idCarritoCompra = $this->generarSiguienteId($this->conexion, "tbcarritocompra", "tbcarritocompraid");

        $sql = "INSERT INTO tbcarritocompra
                (
                    tbcarritocompraid,
                    tbclienteid,
                    tblocalid,
                    tbcarritocompraregistrofecha,
                    tbcarritocompraactualizacionfecha,
                    tbcarritocompraactivo
                )
                VALUES (:id, :idCliente, :idLocal, NOW(), NOW(), 1)";
        $this->conexion->prepare($sql)->execute([
            ":id" => $idCarritoCompra,
            ":idCliente" => $idCliente,
            ":idLocal" => $idLocal
        ]);

        $carritoCompra = $this->buscarPorClienteYLocal($idCliente, $idLocal);
        if ($carritoCompra === null) {
            throw new RuntimeException("No se pudo crear el carrito, intenta de nuevo");
        }
        return $carritoCompra;
    }

    public function guardarCantidad(CarritoCompra $carritoCompra, int $idProducto, int $cantidad): void
    {
        $detalle = $carritoCompra->obtenerDetalle($idProducto);

        if ($detalle !== null) {
            $sql = "UPDATE tbcarritocompradetalle
                    SET tbcarritocompradetallecantidad = :cantidad
                    WHERE tbcarritocompradetalleid = :id";
            $this->conexion->prepare($sql)->execute([
                ":cantidad" => $cantidad,
                ":id" => $detalle->getIdCarritoCompraDetalle()
            ]);
        } else {
            $idDetalle = $this->generarSiguienteId($this->conexion, "tbcarritocompradetalle", "tbcarritocompradetalleid");

            $sql = "INSERT INTO tbcarritocompradetalle
                    (
                        tbcarritocompradetalleid,
                        tbcarritocompraid,
                        tbproductoid,
                        tbcarritocompradetallecantidad,
                        tbcarritocompradetalleregistrofecha,
                        tbcarritocompradetalleactivo
                    )
                    VALUES (:id, :idCarritoCompra, :idProducto, :cantidad, NOW(), 1)";
            $this->conexion->prepare($sql)->execute([
                ":id" => $idDetalle,
                ":idCarritoCompra" => $carritoCompra->getIdCarritoCompra(),
                ":idProducto" => $idProducto,
                ":cantidad" => $cantidad
            ]);
        }

        $this->marcarActualizado($carritoCompra->getIdCarritoCompra());
    }

    public function quitarProducto(int $idCarritoCompra, int $idProducto): bool
    {
        $sql = "UPDATE tbcarritocompradetalle
                SET tbcarritocompradetalleactivo = 0
                WHERE tbcarritocompraid = :idCarritoCompra
                  AND tbproductoid = :idProducto
                  AND tbcarritocompradetalleactivo = 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCarritoCompra" => $idCarritoCompra, ":idProducto" => $idProducto]);

        $this->marcarActualizado($idCarritoCompra);
        return $consulta->rowCount() > 0;
    }

    public function eliminar(int $idCarritoCompra): void
    {
        $this->conexion->prepare("UPDATE tbcarritocompradetalle SET tbcarritocompradetalleactivo = 0 WHERE tbcarritocompraid = :id")
            ->execute([":id" => $idCarritoCompra]);
        $this->conexion->prepare("UPDATE tbcarritocompra SET tbcarritocompraactivo = 0, tbcarritocompraactualizacionfecha = NOW() WHERE tbcarritocompraid = :id")
            ->execute([":id" => $idCarritoCompra]);
    }

    public function contarProductosDeCliente(int $idCliente): int
    {
        $sql = "SELECT COUNT(*)
                FROM tbcarritocompradetalle d
                INNER JOIN tbcarritocompra c ON c.tbcarritocompraid = d.tbcarritocompraid
                WHERE c.tbclienteid = :idCliente
                  AND c.tbcarritocompraactivo = 1
                  AND d.tbcarritocompradetalleactivo = 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);
        return (int) $consulta->fetchColumn();
    }

    private function marcarActualizado(int $idCarritoCompra): void
    {
        $this->conexion->prepare("UPDATE tbcarritocompra SET tbcarritocompraactualizacionfecha = NOW() WHERE tbcarritocompraid = :id")
            ->execute([":id" => $idCarritoCompra]);
    }

    private function mapearConDetalles(array $fila): CarritoCompra
    {
        $carritoCompra = new CarritoCompra(
            (int) $fila["tbclienteid"],
            (int) $fila["tblocalid"],
            (int) $fila["tbcarritocompraid"],
            $fila["tbcarritocompraregistrofecha"] ? new DateTime($fila["tbcarritocompraregistrofecha"]) : null,
            $fila["tbcarritocompraactualizacionfecha"] ? new DateTime($fila["tbcarritocompraactualizacionfecha"]) : null
        );

        $sql = "SELECT * FROM tbcarritocompradetalle
                WHERE tbcarritocompraid = :idCarritoCompra
                  AND tbcarritocompradetalleactivo = 1
                ORDER BY tbcarritocompradetalleid";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCarritoCompra" => $carritoCompra->getIdCarritoCompra()]);

        while ($detalle = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $carritoCompra->agregarDetalle(new CarritoCompraDetalle(
                (int) $detalle["tbproductoid"],
                (int) $detalle["tbcarritocompradetallecantidad"],
                (int) $detalle["tbcarritocompraid"],
                (int) $detalle["tbcarritocompradetalleid"],
                $detalle["tbcarritocompradetalleregistrofecha"] ? new DateTime($detalle["tbcarritocompradetalleregistrofecha"]) : null
            ));
        }

        return $carritoCompra;
    }
}