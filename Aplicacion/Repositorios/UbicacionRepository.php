<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";

class UbicacionRepository
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Ubicacion $ubicacion): bool
    {
        $sql = "INSERT INTO tbubicacion
                (
                    tblocalid,
                    tbubicacionprovincia,
                    tbubicacioncanton,
                    tbubicaciondistrito,
                    tbubicaciondireccionexacta,
                    tbubicacionreferencia,
                    tbubicacionactivo
                )
                VALUES
                (
                    :idLocal,
                    :provincia,
                    :canton,
                    :distrito,
                    :direccionExacta,
                    :referencia,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idLocal" => $ubicacion->getIdLocal(),
            ":provincia" => $ubicacion->getProvincia(),
            ":canton" => $ubicacion->getCanton(),
            ":distrito" => $ubicacion->getDistrito(),
            ":direccionExacta" => $ubicacion->getDireccionExacta(),
            ":referencia" => $ubicacion->getReferencia(),
            ":activo" => $ubicacion->isActivo()
        ]);
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT *
                FROM tbubicacion
                ORDER BY tbubicacionprovincia, tbubicacioncanton";

        $consulta = $this->conexion->query($sql);

        $ubicaciones = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $ubicaciones[] = new Ubicacion(
                (int) $fila["tblocalid"],
                $fila["tbubicacionprovincia"],
                $fila["tbubicacioncanton"],
                $fila["tbubicaciondistrito"],
                $fila["tbubicaciondireccionexacta"],
                $fila["tbubicacionreferencia"],
                (bool) $fila["tbubicacionactivo"],
                (int) $fila["tbubicacionid"]
            );
        }

        return $ubicaciones;
    }

    public function obtenerPorId(int $idUbicacion): ?Ubicacion
    {
        $sql = "SELECT *
                FROM tbubicacion
                WHERE tbubicacionid = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id" => $idUbicacion
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Ubicacion(
            (int) $fila["tblocalid"],
            $fila["tbubicacionprovincia"],
            $fila["tbubicacioncanton"],
            $fila["tbubicaciondistrito"],
            $fila["tbubicaciondireccionexacta"],
            $fila["tbubicacionreferencia"],
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );
    }

    public function obtenerPorLocal(int $idLocal): ?Ubicacion
    {
        $sql = "SELECT *
                FROM tbubicacion
                WHERE tblocalid = :idLocal";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":idLocal" => $idLocal
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Ubicacion(
            (int) $fila["tblocalid"],
            $fila["tbubicacionprovincia"],
            $fila["tbubicacioncanton"],
            $fila["tbubicaciondistrito"],
            $fila["tbubicaciondireccionexacta"],
            $fila["tbubicacionreferencia"],
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );
    }

    public function actualizar(Ubicacion $ubicacion): bool
    {
        $sql = "UPDATE tbubicacion
                SET
                    tbubicacionprovincia = :provincia,
                    tbubicacioncanton = :canton,
                    tbubicaciondistrito = :distrito,
                    tbubicaciondireccionexacta = :direccionExacta,
                    tbubicacionreferencia = :referencia,
                    tbubicacionactivo = :activo
                WHERE tblocalid = :idLocal";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":provincia" => $ubicacion->getProvincia(),
            ":canton" => $ubicacion->getCanton(),
            ":distrito" => $ubicacion->getDistrito(),
            ":direccionExacta" => $ubicacion->getDireccionExacta(),
            ":referencia" => $ubicacion->getReferencia(),
            ":activo" => $ubicacion->isActivo(),
            ":idLocal" => $ubicacion->getIdLocal()
        ]);
    }

    public function eliminar(int $idLocal): bool
    {
        $sql = "UPDATE tbubicacion
                SET tbubicacionactivo = 0
                WHERE tblocalid = :idLocal";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idLocal" => $idLocal
        ]);
    }
}