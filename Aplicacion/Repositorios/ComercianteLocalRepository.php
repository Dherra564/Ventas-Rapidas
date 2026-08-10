<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/ComercianteLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
class ComercianteLocalRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function insertar(ComercianteLocal $comercianteLocal): int|false
    {
        $this->validarReferencia(
            $this->conexion,
            "tbcomerciante",
            "tbcomercianteid",
            $comercianteLocal->getIdComerciante(),
            "El comerciante con ID {$comercianteLocal->getIdComerciante()} no existe"
        );

        $this->validarReferencia(
            $this->conexion,
            "tblocal",
            "tblocalid",
            $comercianteLocal->getIdLocal(),
            "El local con ID {$comercianteLocal->getIdLocal()} no existe"
        );

        $id = $this->generarSiguienteId($this->conexion, "tbcomerciantelocal", "tbcomerciantelocalid");

        $sql = "INSERT INTO tbcomerciantelocal
                (tbcomerciantelocalid, tbcomercianteid, tblocalid, tbcomerciantelocalactivo)
                VALUES (:id, :idComerciante, :idLocal, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idComerciante" => $comercianteLocal->getIdComerciante(),
            ":idLocal" => $comercianteLocal->getIdLocal(),
            ":activo" => $comercianteLocal->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function existeComercianteActivoParaLocal(int $idLocal): bool
    {
        $sql = "SELECT COUNT(*) AS total
                FROM tbcomerciantelocal
                WHERE tblocalid = :idLocal
                AND tbcomerciantelocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return ((int) $fila["total"]) > 0;
    }

    public function obtenerComerciantePorLocal(int $idLocal): ?int
    {
        $sql = "SELECT tbcomercianteid
                FROM tbcomerciantelocal
                WHERE tblocalid = :idLocal
                AND tbcomerciantelocalactivo = 1
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? (int) $fila["tbcomercianteid"] : null;
    }
}