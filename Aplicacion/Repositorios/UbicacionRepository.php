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
        if (!$ubicacion->tieneDuenoValido()) {
            throw new InvalidArgumentException(
                "La ubicación debe pertenecer a un local o a un cliente, no a ambos ni a ninguno"
            );
        }

        if ($ubicacion->getIdLocal() !== null && $ubicacion->getIdLocal() > 0) {
            $this->validarReferencia(
                $this->conexion,
                "tblocal",
                "tblocalid",
                $ubicacion->getIdLocal(),
                "El local con ID {$ubicacion->getIdLocal()} no existe"
            );
        }

        if ($ubicacion->getIdCliente() !== null && $ubicacion->getIdCliente() > 0) {
            $this->validarReferencia(
                $this->conexion,
                "tbcliente",
                "tbclienteid",
                $ubicacion->getIdCliente(),
                "El cliente con ID {$ubicacion->getIdCliente()} no existe"
            );
        }

        $this->validarReferencia($this->conexion, "tbprovincia", "tbprovinciaid", $ubicacion->getIdProvincia(), "La provincia con ID {$ubicacion->getIdProvincia()} no existe");
        $this->validarReferencia($this->conexion, "tbcanton", "tbcantonid", $ubicacion->getIdCanton(), "El cantón con ID {$ubicacion->getIdCanton()} no existe");
        $this->validarReferencia($this->conexion, "tbdistrito", "tbdistritoid", $ubicacion->getIdDistrito(), "El distrito con ID {$ubicacion->getIdDistrito()} no existe");

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
        $sql = "SELECT * FROM tbubicacion WHERE tblocalid = :idLocal ORDER BY tbubicacionid DESC LIMIT 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorCliente(int $idCliente): ?Ubicacion
    {
        $sql = "SELECT * FROM tbubicacion WHERE tbclienteid = :idCliente ORDER BY tbubicacionid DESC LIMIT 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function actualizar(Ubicacion $ubicacion): bool
    {
        if (!$ubicacion->tieneDuenoValido()) {
            throw new InvalidArgumentException("La ubicación debe pertenecer a un local o a un cliente");
        }

        $this->validarReferencia($this->conexion, "tbprovincia", "tbprovinciaid", $ubicacion->getIdProvincia(), "La provincia con ID {$ubicacion->getIdProvincia()} no existe");
        $this->validarReferencia($this->conexion, "tbcanton", "tbcantonid", $ubicacion->getIdCanton(), "El cantón con ID {$ubicacion->getIdCanton()} no existe");
        $this->validarReferencia($this->conexion, "tbdistrito", "tbdistritoid", $ubicacion->getIdDistrito(), "El distrito con ID {$ubicacion->getIdDistrito()} no existe");

        if ($ubicacion->getIdUbicacion() > 0) {
            $where = "tbubicacionid = :idUbicacion";
            $idParams = [":idUbicacion" => $ubicacion->getIdUbicacion()];
        } elseif ($ubicacion->getIdLocal() !== null && $ubicacion->getIdLocal() > 0) {
            $where = "tblocalid = :idLocal";
            $idParams = [":idLocal" => $ubicacion->getIdLocal()];
        } else {
            $where = "tbclienteid = :idClienteWhere";
            $idParams = [":idClienteWhere" => $ubicacion->getIdCliente()];
        }

        $sql = "UPDATE tbubicacion
                SET tblocalid = :idLocal,
                    tbclienteid = :idCliente,
                    tbprovinciaid = :idProvincia,
                    tbcantonid = :idCanton,
                    tbdistritoid = :idDistrito,
                    tbubicaciondireccionexacta = :direccionExacta,
                    tbubicaciondereferencia = :referencia,
                    tbubicacionactivo = :activo
                WHERE {$where}";

        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute(array_merge([
            ":idLocal" => $ubicacion->getIdLocal(),
            ":idCliente" => $ubicacion->getIdCliente(),
            ":idProvincia" => $ubicacion->getIdProvincia(),
            ":idCanton" => $ubicacion->getIdCanton(),
            ":idDistrito" => $ubicacion->getIdDistrito(),
            ":direccionExacta" => $ubicacion->getDireccionExacta(),
            ":referencia" => $ubicacion->getReferencia(),
            ":activo" => $ubicacion->isActivo()
        ], $idParams));
    }

    private function mapearFila(array $fila): Ubicacion
    {
        return new Ubicacion(
            $fila["tblocalid"] !== null ? (int) $fila["tblocalid"] : null,
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
}
