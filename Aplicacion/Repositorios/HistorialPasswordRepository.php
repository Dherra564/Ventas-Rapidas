<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/HistorialPassword.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class HistorialPasswordRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(HistorialPassword $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbhistorialpassword", "tbhistorialpasswordid");

        $sql = "INSERT INTO tbhistorialpassword
                (
                    tbhistorialpasswordid,
                    tbhistorialpasswordusuarioid,
                    tbhistorialpasswordusuariotipo,
                    tbhistorialpasswordexitoso,
                    tbhistorialpasswordactivo
                )
                VALUES
                (
                    :id,
                    :idUsuario,
                    :tipoUsuario,
                    :exitoso,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUsuario" => $historial->getIdUsuario(),
            ":tipoUsuario" => $historial->getTipoUsuario(),
            ":exitoso" => $historial->isExitoso(),
            ":activo" => $historial->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        $sql = "SELECT * FROM tbhistorialpassword
                WHERE tbhistorialpasswordusuarioid = :idUsuario
                  AND tbhistorialpasswordusuariotipo = :tipoUsuario
                ORDER BY tbhistorialpasswordfecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        return $this->mapearFilas($consulta);
    }

    public function contarIntentosFallidosRecientes(int $idUsuario, string $tipoUsuario, int $horas): int
    {
        $sql = "SELECT COUNT(*) FROM tbhistorialpassword
                WHERE tbhistorialpasswordusuarioid = :idUsuario
                  AND tbhistorialpasswordusuariotipo = :tipoUsuario
                  AND tbhistorialpasswordexitoso = 0
                  AND tbhistorialpasswordfecha >= (NOW() - INTERVAL :horas HOUR)";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idUsuario", $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(":tipoUsuario", $tipoUsuario, PDO::PARAM_STR);
        $consulta->bindValue(":horas", $horas, PDO::PARAM_INT);
        $consulta->execute();

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

    private function mapearFila(array $fila): HistorialPassword
    {
        return new HistorialPassword(
            (int) $fila["tbhistorialpasswordusuarioid"],
            $fila["tbhistorialpasswordusuariotipo"],
            (bool) $fila["tbhistorialpasswordexitoso"],
            (bool) $fila["tbhistorialpasswordactivo"],
            (int) $fila["tbhistorialpasswordid"],
            new DateTime($fila["tbhistorialpasswordfecha"])
        );
    }
}