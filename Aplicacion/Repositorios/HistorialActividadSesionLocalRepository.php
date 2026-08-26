<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/HistorialActividadSesionLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class HistorialActividadSesionLocalRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(HistorialActividadSesionLocal $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbhistorialactividadsesionlocal", "tbhistorialactividadsesionlocalid");

        $sql = "INSERT INTO tbhistorialactividadsesionlocal
                (tbhistorialactividadsesionlocalid, tblocalid, tbhistorialactividadsesionlocaltipo)
                VALUES (:id, :idLocal, :tipo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idLocal" => $historial->getIdLocal(),
            ":tipo" => $historial->getTipo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorLocal(int $idLocal, int $limite = 50): array
    {
        $sql = "SELECT * FROM tbhistorialactividadsesionlocal
                WHERE tblocalid = :idLocal
                ORDER BY tbhistorialactividadsesionlocalfecha DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idLocal", $idLocal, PDO::PARAM_INT);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $this->mapearFilas($consulta);
    }

    public function tieneActividadReciente(int $idLocal, int $dias = 7): bool
    {
        $sql = "SELECT COUNT(*) FROM tbhistorialactividadsesionlocal
                WHERE tblocalid = :idLocal
                  AND tbhistorialactividadsesionlocalfecha >= (NOW() - INTERVAL :dias DAY)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idLocal", $idLocal, PDO::PARAM_INT);
        $consulta->bindValue(":dias", $dias, PDO::PARAM_INT);
        $consulta->execute();

        return (int) $consulta->fetchColumn() > 0;
    }

    public function obtenerActividadRecientePorLote(array $idsLocales, int $dias = 7): array
    {
        if (empty($idsLocales)) {
            return [];
        }

        $placeholders = implode(",", array_fill(0, count($idsLocales), "?"));
        $sql = "SELECT DISTINCT tblocalid FROM tbhistorialactividadsesionlocal
                WHERE tblocalid IN ($placeholders)
                  AND tbhistorialactividadsesionlocalfecha >= (NOW() - INTERVAL $dias DAY)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($idsLocales);
        $activos = $consulta->fetchAll(PDO::FETCH_COLUMN);

        $mapa = [];
        foreach ($idsLocales as $idLocal) {
            $mapa[$idLocal] = in_array($idLocal, $activos);
        }

        return $mapa;
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): HistorialActividadSesionLocal
    {
        return new HistorialActividadSesionLocal(
            (int) $fila["tblocalid"],
            $fila["tbhistorialactividadsesionlocaltipo"],
            (int) $fila["tbhistorialactividadsesionlocalid"],
            new DateTime($fila["tbhistorialactividadsesionlocalfecha"])
        );
    }
}