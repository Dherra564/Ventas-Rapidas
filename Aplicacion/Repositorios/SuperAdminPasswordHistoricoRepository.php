<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/SuperAdminPasswordHistorico.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class SuperAdminPasswordHistoricoRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    // Registra un intento de inicio de sesión (exitoso o no).
    public function registrarIntentoLogin(int $idSuperAdmin, bool $exitoso): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbsuperadminpasswordhistorico", "tbsuperadminpasswordhistoricoid");

        $sql = "INSERT INTO tbsuperadminpasswordhistorico
                (tbsuperadminpasswordhistoricoid, tbsuperadminid, exitoso, fecha)
                VALUES (:id, :idSuperAdmin, :exitoso, NOW())";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idSuperAdmin" => $idSuperAdmin,
            ":exitoso" => (int) $exitoso
        ]);

        return $exito ? $id : false;
    }

    public function contarIntentosFallidosRecientes(int $idSuperAdmin, int $horas = 1): int
    {
        $sql = "SELECT COUNT(*) FROM tbsuperadminpasswordhistorico
                WHERE tbsuperadminid = :idSuperAdmin
                  AND exitoso = 0
                  AND fecha >= (NOW() - INTERVAL :horas HOUR)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idSuperAdmin", $idSuperAdmin, PDO::PARAM_INT);
        $consulta->bindValue(":horas", $horas, PDO::PARAM_INT);
        $consulta->execute();

        return (int) $consulta->fetchColumn();
    }

    public function obtenerPorSuperAdmin(int $idSuperAdmin): array
    {
        $sql = "SELECT * FROM tbsuperadminpasswordhistorico
                WHERE tbsuperadminid = :idSuperAdmin
                ORDER BY fecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idSuperAdmin" => $idSuperAdmin]);

        $registros = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = new SuperAdminPasswordHistorico(
                (int) $fila["tbsuperadminid"],
                (bool) $fila["exitoso"],
                (int) $fila["tbsuperadminpasswordhistoricoid"],
                new DateTime($fila["fecha"])
            );
        }

        return $registros;
    }
}