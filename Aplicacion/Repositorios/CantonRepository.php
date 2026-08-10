<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Canton.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";

class CantonRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Canton $canton): int|false
    {
        $this->validarReferencia(
            $this->conexion,
            "tbprovincia",
            "tbprovinciaid",
            $canton->getIdProvincia(),
            "La provincia con ID {$canton->getIdProvincia()} no existe"
        );

        $id = $this->generarSiguienteId($this->conexion, "tbcanton", "tbcantonid");

        $sql = "INSERT INTO tbcanton
                (tbcantonid, tbprovinciaid, tbcantonnombre, tbcantonactivo)
                VALUES (:id, :idProvincia, :nombre, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idProvincia" => $canton->getIdProvincia(),
            ":nombre" => $canton->getNombre(),
            ":activo" => $canton->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcanton ORDER BY tbcantonnombre";

        $consulta = $this->conexion->query($sql);

        $cantones = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $cantones[] = new Canton(
                (int) $fila["tbprovinciaid"],
                $fila["tbcantonnombre"],
                (bool) $fila["tbcantonactivo"],
                (int) $fila["tbcantonid"]
            );
        }

        return $cantones;
    }

    public function obtenerPorId(int $idCanton): ?Canton
    {
        $sql = "SELECT * FROM tbcanton WHERE tbcantonid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idCanton]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Canton(
            (int) $fila["tbprovinciaid"],
            $fila["tbcantonnombre"],
            (bool) $fila["tbcantonactivo"],
            (int) $fila["tbcantonid"]
        );
    }

    public function obtenerPorProvincia(int $idProvincia): array
    {
        $sql = "SELECT * FROM tbcanton
                WHERE tbprovinciaid = :idProvincia
                AND tbcantonactivo = 1
                ORDER BY tbcantonnombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idProvincia" => $idProvincia]);

        $cantones = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $cantones[] = new Canton(
                (int) $fila["tbprovinciaid"],
                $fila["tbcantonnombre"],
                (bool) $fila["tbcantonactivo"],
                (int) $fila["tbcantonid"]
            );
        }

        return $cantones;
    }

    public function actualizar(Canton $canton): bool
    {
        $this->validarReferencia(
            $this->conexion,
            "tbprovincia",
            "tbprovinciaid",
            $canton->getIdProvincia(),
            "La provincia con ID {$canton->getIdProvincia()} no existe"
        );

        $sql = "UPDATE tbcanton
                SET tbprovinciaid = :idProvincia, tbcantonnombre = :nombre, tbcantonactivo = :activo
                WHERE tbcantonid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idProvincia" => $canton->getIdProvincia(),
            ":nombre" => $canton->getNombre(),
            ":activo" => $canton->isActivo(),
            ":id" => $canton->getIdCanton()
        ]);
    }

    public function eliminar(int $idCanton): bool
    {
        $sql = "UPDATE tbcanton SET tbcantonactivo = 0 WHERE tbcantonid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idCanton]);
    }
}