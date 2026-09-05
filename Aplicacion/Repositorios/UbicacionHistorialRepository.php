<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/UbicacionHistorial.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class UbicacionHistorialRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(UbicacionHistorial $historial): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbubicacioncoordenadashistorico", "tbubicacioncoordenadashistoricoid");

        $sql = "INSERT INTO tbubicacioncoordenadashistorico
            (
                tbubicacioncoordenadashistoricoid,
                tbubicacionid,
                idusuario,
                tipousuario,
                latitudanterior,
                longitudanterior,
                latitudnueva,
                longitudnueva,
                fecha
            )
            VALUES
            (
                :id,
                :idUbicacion,
                :idUsuario,
                :tipoUsuario,
                :latitudAnterior,
                :longitudAnterior,
                :latitudNueva,
                :longitudNueva,
                NOW()
            )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUbicacion" => $historial->getIdUbicacion(),
            ":idUsuario" => $historial->getIdUsuario(),
            ":tipoUsuario" => $historial->getTipoUsuario(),
            ":latitudAnterior" => $historial->getLatitudAnterior(),
            ":longitudAnterior" => $historial->getLongitudAnterior(),
            ":latitudNueva" => $historial->getLatitudNueva(),
            ":longitudNueva" => $historial->getLongitudNueva()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerUltimoPorUsuario(int $idUsuario, string $tipoUsuario): ?UbicacionHistorial
    {
        $sql = "SELECT * FROM tbubicacioncoordenadashistorico
                WHERE idusuario = :idUsuario
                  AND tipousuario = :tipoUsuario
                ORDER BY fecha DESC, tbubicacioncoordenadashistoricoid DESC
                LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        $sql = "SELECT * FROM tbubicacioncoordenadashistorico
                WHERE idusuario = :idUsuario
                  AND tipousuario = :tipoUsuario
                ORDER BY fecha DESC, tbubicacioncoordenadashistoricoid DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        $registros = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila);
        }

        return $registros;
    }

    private function mapearFila(array $fila): UbicacionHistorial
    {
        return new UbicacionHistorial(
            $fila["tbubicacionid"] !== null ? (int) $fila["tbubicacionid"] : null,
            (int) $fila["idusuario"],
            $fila["tipousuario"],
            $fila["latitudanterior"] !== null ? (float) $fila["latitudanterior"] : null,
            $fila["longitudanterior"] !== null ? (float) $fila["longitudanterior"] : null,
            (float) $fila["latitudnueva"],
            (float) $fila["longitudnueva"],
            (int) $fila["tbubicacioncoordenadashistoricoid"],
            new DateTime($fila["fecha"])
        );
    }
}