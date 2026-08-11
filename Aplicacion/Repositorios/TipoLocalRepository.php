<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/TipoLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class TipoLocalRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(TipoLocal $tipoLocal): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tblocaltipo", "tblocaltipoid");

        $sql = "INSERT INTO tblocaltipo (tblocaltipoid, tblocaltiponombre) VALUES (:id, :nombre)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $tipoLocal->getNombre()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tblocaltipo ORDER BY tblocaltiponombre";

        $consulta = $this->conexion->query($sql);

        $tipos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $tipos[] = new TipoLocal(
                $fila["tblocaltiponombre"],
                (int) $fila["tblocaltipoid"]
            );
        }

        return $tipos;
    }

    public function buscarPorNombre(string $textoParcial): array
    {
        $sql = "SELECT * FROM tblocaltipo
                WHERE tblocaltiponombre LIKE :texto
                ORDER BY tblocaltiponombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":texto" => "%{$textoParcial}%"]);

        $tipos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $tipos[] = new TipoLocal(
                $fila["tblocaltiponombre"],
                (int) $fila["tblocaltipoid"]
            );
        }

        return $tipos;
    }

    public function obtenerPorNombreExacto(string $nombre): ?TipoLocal
    {
        $sql = "SELECT * FROM tblocaltipo WHERE tblocaltiponombre = :nombre LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":nombre" => $nombre]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new TipoLocal(
            $fila["tblocaltiponombre"],
            (int) $fila["tblocaltipoid"]
        );
    }

    public function obtenerPorId(int $idTipoLocal): ?TipoLocal
    {
        $sql = "SELECT * FROM tblocaltipo WHERE tblocaltipoid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idTipoLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new TipoLocal(
            $fila["tblocaltiponombre"],
            (int) $fila["tblocaltipoid"]
        );
    }
    
}