<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/SesionActivaHistorico.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class SesionActicoHistoricoRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(SesionActivaHistorico $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbsesionactivohistorico", "tbsesionactivohistoricoid");

        $sql = "INSERT INTO tbsesionactivohistorico
                (tbsesionactivohistoricoid, tbsesionid, tblocalid, valoranterior, valornuevo, fecha, activo)
                VALUES (:id, :idSesion, :idLocal, :valorAnterior, :valorNuevo, NOW(), :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idSesion" => $historial->getIdSesion(),
            ":idLocal" => $historial->getIdLocal(),
            ":valorAnterior" => (int) $historial->isValorAnterior(),
            ":valorNuevo" => (int) $historial->isValorNuevo(),
            ":activo" => (int) $historial->isActivo()
        ]);

        return $exito ? $id : false;
    }


    public function registrarCambioActivo(int $idSesion, int $idLocal, bool $valorAnterior, bool $valorNuevo): int|false
    {
        $historial = new SesionActivaHistorico($idSesion, $idLocal, $valorAnterior, $valorNuevo);
        return $this->registrar($historial);
    }

    public function obtenerPorLocal(int $idLocal, int $limite = 50): array
    {
        $sql = "SELECT * FROM tbsesionactivohistorico
                WHERE tblocalid = :idLocal
                ORDER BY fecha DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idLocal", $idLocal, PDO::PARAM_INT);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $this->mapearFilas($consulta);
    }

    public function tieneActividadReciente(int $idLocal, int $dias = 7): bool
    {
        $sql = "SELECT COUNT(*) FROM tbsesionactivohistorico
                WHERE tblocalid = :idLocal
                  AND valornuevo = 1
                  AND fecha >= (NOW() - INTERVAL :dias DAY)";

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
        $sql = "SELECT DISTINCT tblocalid FROM tbsesionactivohistorico
                WHERE tblocalid IN ($placeholders)
                  AND valornuevo = 1
                  AND fecha >= (NOW() - INTERVAL $dias DAY)";

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

    private function mapearFila(array $fila): SesionActivaHistorico
    {
        return new SesionActivaHistorico(
            (int) $fila["tbsesionid"],
            (int) $fila["tblocalid"],
            (bool) $fila["valoranterior"],
            (bool) $fila["valornuevo"],
            (bool) $fila["activo"],
            (int) $fila["tbsesionactivohistoricoid"],
            new DateTime($fila["fecha"])
        );
    }
}