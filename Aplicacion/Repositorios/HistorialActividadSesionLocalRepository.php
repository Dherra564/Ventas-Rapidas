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
                (tbhistorialactividadsesionlocalid, tbhistorialactividadsesionlocalusuarioid, tbhistorialactividadsesionlocalusuariotipo, tblocalid, tbhistorialactividadsesionlocaltipo)
                VALUES (:id, :idUsuario, :tipoUsuario, :idLocal, :tipo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUsuario" => $historial->getIdUsuario(),
            ":tipoUsuario" => $historial->getTipoUsuario(),
            ":idLocal" => $historial->getIdLocal(),
            ":tipo" => $historial->getTipo()
        ]);

        return $exito ? $id : false;
    }

    public function registrarLogin(int $idUsuario, string $tipoUsuario): int|false
    {
        $historial = new HistorialActividadSesionLocal($idUsuario, $tipoUsuario, HistorialActividadSesionLocal::TIPO_LOGIN);
        return $this->registrar($historial);
    }

    public function registrarEntradaPerfil(int $idComerciante, int $idLocal): int|false
    {
        $historial = new HistorialActividadSesionLocal($idComerciante, 'Comerciante', HistorialActividadSesionLocal::TIPO_ENTRADA_PERFIL, $idLocal);
        return $this->registrar($historial);
    }

    public function obtenerPorLocal(int $idLocal, int $limite = 50): array
    {
        $sql = "SELECT * FROM tbhistorialactividadsesionlocal
                WHERE tblocalid = :idLocal
                  AND tbhistorialactividadsesionlocaltipo = :tipo
                ORDER BY tbhistorialactividadsesionlocalfecha DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idLocal", $idLocal, PDO::PARAM_INT);
        $consulta->bindValue(":tipo", HistorialActividadSesionLocal::TIPO_ENTRADA_PERFIL);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $this->mapearFilas($consulta);
    }

    public function obtenerSesionesPorUsuario(int $idUsuario, string $tipoUsuario, int $limite = 50): array
    {
        $sql = "SELECT * FROM tbhistorialactividadsesionlocal
                WHERE tbhistorialactividadsesionlocalusuarioid = :idUsuario
                  AND tbhistorialactividadsesionlocalusuariotipo = :tipoUsuario
                  AND tbhistorialactividadsesionlocaltipo = :tipo
                ORDER BY tbhistorialactividadsesionlocalfecha DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idUsuario", $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(":tipoUsuario", $tipoUsuario);
        $consulta->bindValue(":tipo", HistorialActividadSesionLocal::TIPO_LOGIN);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        return $this->mapearFilas($consulta);
    }

    public function tieneActividadReciente(int $idLocal, int $dias = 7): bool
    {
        $sql = "SELECT COUNT(*) FROM tbhistorialactividadsesionlocal
                WHERE tblocalid = :idLocal
                  AND tbhistorialactividadsesionlocaltipo = :tipo
                  AND tbhistorialactividadsesionlocalfecha >= (NOW() - INTERVAL :dias DAY)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idLocal", $idLocal, PDO::PARAM_INT);
        $consulta->bindValue(":tipo", HistorialActividadSesionLocal::TIPO_ENTRADA_PERFIL);
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
                  AND tbhistorialactividadsesionlocaltipo = '" . HistorialActividadSesionLocal::TIPO_ENTRADA_PERFIL . "'
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
            (int) $fila["tbhistorialactividadsesionlocalusuarioid"],
            $fila["tbhistorialactividadsesionlocalusuariotipo"],
            $fila["tbhistorialactividadsesionlocaltipo"],
            $fila["tblocalid"] !== null ? (int) $fila["tblocalid"] : null,
            (int) $fila["tbhistorialactividadsesionlocalid"],
            new DateTime($fila["tbhistorialactividadsesionlocalfecha"])
        );
    }
}