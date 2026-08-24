<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";

class UbicacionRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function insertar(Ubicacion $ubicacion): int|false
    {
        $this->validarReferencia($this->conexion, "tblocal", "tblocalid", $ubicacion->getIdLocal(), "El local con ID {$ubicacion->getIdLocal()} no existe");
        $this->validarReferencia($this->conexion, "tbprovincia", "tbprovinciaid", $ubicacion->getIdProvincia(), "La provincia con ID {$ubicacion->getIdProvincia()} no existe");
        $this->validarReferencia($this->conexion, "tbcanton", "tbcantonid", $ubicacion->getIdCanton(), "El cantón con ID {$ubicacion->getIdCanton()} no existe");
        $this->validarReferencia($this->conexion, "tbdistrito", "tbdistritoid", $ubicacion->getIdDistrito(), "El distrito con ID {$ubicacion->getIdDistrito()} no existe");

        if ($ubicacion->getIdCliente() !== null) {
            $this->validarReferencia($this->conexion, "tbcliente", "tbclienteid", $ubicacion->getIdCliente(), "El cliente con ID {$ubicacion->getIdCliente()} no existe");
        }

        $id = $this->generarSiguienteId($this->conexion, "tbubicacion", "tbubicacionid");

        $sql = "INSERT INTO tbubicacion
                (
                    tbubicacionid,
                    tblocalid,
                    tbclienteid,
                    tbprovinciaid,
                    tbcantonid,
                    tbdistritoid,
                    tbubicaciondireccionexacta,
                    tbubicaciondereferencia,
                    tbubicacionactivo
                )
                VALUES
                (
                    :id,
                    :idLocal,
                    :idCliente,
                    :idProvincia,
                    :idCanton,
                    :idDistrito,
                    :direccionExacta,
                    :referencia,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idLocal" => $ubicacion->getIdLocal(),
            ":idCliente" => $ubicacion->getIdCliente(),
            ":idProvincia" => $ubicacion->getIdProvincia(),
            ":idCanton" => $ubicacion->getIdCanton(),
            ":idDistrito" => $ubicacion->getIdDistrito(),
            ":direccionExacta" => $ubicacion->getDireccionExacta(),
            ":referencia" => $ubicacion->getReferencia(),
            ":activo" => $ubicacion->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorLocal(int $idLocal): ?Ubicacion
    {
        $sql = "SELECT * FROM tbubicacion WHERE tblocalid = :idLocal";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Ubicacion(
            (int) $fila["tblocalid"],
            (int) $fila["tbprovinciaid"],
            (int) $fila["tbcantonid"],
            (int) $fila["tbdistritoid"],
            $fila["tbubicaciondireccionexacta"],
            $fila["tbubicaciondereferencia"],
            $fila["tbclienteid"] !== null ? (int) $fila["tbclienteid"] : null,
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );
    }

    public function actualizar(Ubicacion $ubicacion): bool
    {
        $this->validarReferencia($this->conexion, "tbprovincia", "tbprovinciaid", $ubicacion->getIdProvincia(), "La provincia con ID {$ubicacion->getIdProvincia()} no existe");
        $this->validarReferencia($this->conexion, "tbcanton", "tbcantonid", $ubicacion->getIdCanton(), "El cantón con ID {$ubicacion->getIdCanton()} no existe");
        $this->validarReferencia($this->conexion, "tbdistrito", "tbdistritoid", $ubicacion->getIdDistrito(), "El distrito con ID {$ubicacion->getIdDistrito()} no existe");

        if ($ubicacion->getIdCliente() !== null) {
            $this->validarReferencia($this->conexion, "tbcliente", "tbclienteid", $ubicacion->getIdCliente(), "El cliente con ID {$ubicacion->getIdCliente()} no existe");
        }

        $sql = "UPDATE tbubicacion
                SET
                    tbclienteid = :idCliente,
                    tbprovinciaid = :idProvincia,
                    tbcantonid = :idCanton,
                    tbdistritoid = :idDistrito,
                    tbubicaciondireccionexacta = :direccionExacta,
                    tbubicaciondereferencia = :referencia,
                    tbubicacionactivo = :activo
                WHERE tblocalid = :idLocal";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idCliente" => $ubicacion->getIdCliente(),
            ":idProvincia" => $ubicacion->getIdProvincia(),
            ":idCanton" => $ubicacion->getIdCanton(),
            ":idDistrito" => $ubicacion->getIdDistrito(),
            ":direccionExacta" => $ubicacion->getDireccionExacta(),
            ":referencia" => $ubicacion->getReferencia(),
            ":activo" => $ubicacion->isActivo(),
            ":idLocal" => $ubicacion->getIdLocal()
        ]);
    }
}