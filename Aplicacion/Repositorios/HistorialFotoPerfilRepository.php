<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/HistorialFotoPerfil.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class HistorialFotoPerfilRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(HistorialFotoPerfil $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbhistorialfotoperfil", "tbhistorialfotoperfilid");

        $sql = "INSERT INTO tbhistorialfotoperfil
                (
                    tbhistorialfotoperfilid,
                    tbhistorialfotoperfilusuarioid,
                    tbhistorialfotoperfilusuariotipo,
                    tbhistorialfotoperfilrutaanterior,
                    tbhistorialfotoperfilrutanueva,
                    tbhistorialfotoperfilactivo
                )
                VALUES
                (
                    :id,
                    :idUsuario,
                    :tipoUsuario,
                    :rutaAnterior,
                    :rutaNueva,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUsuario" => $historial->getIdUsuario(),
            ":tipoUsuario" => $historial->getTipoUsuario(),
            ":rutaAnterior" => $historial->getRutaAnterior(),
            ":rutaNueva" => $historial->getRutaNueva(),
            ":activo" => $historial->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        $sql = "SELECT * FROM tbhistorialfotoperfil
                WHERE tbhistorialfotoperfilusuarioid = :idUsuario
                  AND tbhistorialfotoperfilusuariotipo = :tipoUsuario
                ORDER BY tbhistorialfotoperfilfecha DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        return $this->mapearFilas($consulta);
    }

    public function obtenerUltimoCambio(int $idUsuario, string $tipoUsuario): ?HistorialFotoPerfil
    {
        $sql = "SELECT * FROM tbhistorialfotoperfil
                WHERE tbhistorialfotoperfilusuarioid = :idUsuario
                  AND tbhistorialfotoperfilusuariotipo = :tipoUsuario
                ORDER BY tbhistorialfotoperfilfecha DESC
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function contarCambiosRecientes(int $idUsuario, string $tipoUsuario, int $horas): int
    {
        $sql = "SELECT COUNT(*) FROM tbhistorialfotoperfil
                WHERE tbhistorialfotoperfilusuarioid = :idUsuario
                  AND tbhistorialfotoperfilusuariotipo = :tipoUsuario
                  AND tbhistorialfotoperfilfecha >= (NOW() - INTERVAL :horas HOUR)";

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

    private function mapearFila(array $fila): HistorialFotoPerfil
    {
        return new HistorialFotoPerfil(
            (int) $fila["tbhistorialfotoperfilusuarioid"],
            $fila["tbhistorialfotoperfilusuariotipo"],
            $fila["tbhistorialfotoperfilrutanueva"],
            $fila["tbhistorialfotoperfilrutaanterior"],
            (bool) $fila["tbhistorialfotoperfilactivo"],
            (int) $fila["tbhistorialfotoperfilid"],
            new DateTime($fila["tbhistorialfotoperfilfecha"])
        );
    }
}