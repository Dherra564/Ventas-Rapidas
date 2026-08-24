<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/RegistroCompra.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";

class RegistroCompraRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(RegistroCompra $registro): int|false
    {
        $this->validarReferencia($this->conexion, "tbcliente", "tbclienteid", $registro->getIdCliente(), "El cliente con ID {$registro->getIdCliente()} no existe");
        $this->validarReferencia($this->conexion, "tblocal", "tblocalid", $registro->getIdLocal(), "El local con ID {$registro->getIdLocal()} no existe");

        $id = $this->generarSiguienteId($this->conexion, "tbregistrocompra", "tbregistrocompraid");

        $sql = "INSERT INTO tbregistrocompra
                (
                    tbregistrocompraid,
                    tbregistrocompraclienteid,
                    tbregistrocompralocalid,
                    tbregistrocompraactivo
                )
                VALUES
                (
                    :id,
                    :idCliente,
                    :idLocal,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idCliente" => $registro->getIdCliente(),
            ":idLocal" => $registro->getIdLocal(),
            ":activo" => $registro->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM tbregistrocompra
                WHERE tbregistrocompraclienteid = :idCliente
                  AND tbregistrocompraactivo = 1
                ORDER BY tbregistrocomprafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $this->mapearFilas($consulta);
    }

    // Compras de un local en un día específico ('Y-m-d') — para la vista de calendario del comerciante.
    public function obtenerPorLocalYFecha(int $idLocal, string $fecha): array
    {
        $sql = "SELECT * FROM tbregistrocompra
                WHERE tbregistrocompralocalid = :idLocal
                  AND tbregistrocompraactivo = 1
                  AND DATE(tbregistrocomprafecha) = :fecha
                ORDER BY tbregistrocomprafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal, ":fecha" => $fecha]);

        return $this->mapearFilas($consulta);
    }

    // Compras de un cliente en un día específico ('Y-m-d') — para la vista de calendario del cliente.
    public function obtenerPorClienteYFecha(int $idCliente, string $fecha): array
    {
        $sql = "SELECT * FROM tbregistrocompra
                WHERE tbregistrocompraclienteid = :idCliente
                  AND tbregistrocompraactivo = 1
                  AND DATE(tbregistrocomprafecha) = :fecha
                ORDER BY tbregistrocomprafecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente, ":fecha" => $fecha]);

        return $this->mapearFilas($consulta);
    }

    public function obtenerLocalesMasComprados(int $limite = 10): array
    {
        $sql = "SELECT tbregistrocompralocalid AS idLocal, COUNT(*) AS totalCompras
                FROM tbregistrocompra
                WHERE tbregistrocompraactivo = 1
                GROUP BY tbregistrocompralocalid
                ORDER BY totalCompras DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerLocalesMasCompradosPorCliente(int $idCliente, int $limite = 10): array
    {
        $sql = "SELECT tbregistrocompralocalid AS idLocal, COUNT(*) AS totalCompras
                FROM tbregistrocompra
                WHERE tbregistrocompraclienteid = :idCliente
                  AND tbregistrocompraactivo = 1
                GROUP BY tbregistrocompralocalid
                ORDER BY totalCompras DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idCliente", $idCliente, PDO::PARAM_INT);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contarComprasPorLocal(int $idLocal): int
    {
        $sql = "SELECT COUNT(*) FROM tbregistrocompra
                WHERE tbregistrocompralocalid = :idLocal
                  AND tbregistrocompraactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return (int) $consulta->fetchColumn();
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): RegistroCompra
    {
        return new RegistroCompra(
            (int) $fila["tbregistrocompraclienteid"],
            (int) $fila["tbregistrocompralocalid"],
            (bool) $fila["tbregistrocompraactivo"],
            (int) $fila["tbregistrocompraid"],
            new DateTime($fila["tbregistrocomprafecha"])
        );
    }
}