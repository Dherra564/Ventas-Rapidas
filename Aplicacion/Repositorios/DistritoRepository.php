<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Distrito.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
class DistritoRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Distrito $distrito): int|false
    {
        $this->validarReferencia(
            $this->conexion,
            "tbcanton",
            "tbcantonid",
            $distrito->getIdCanton(),
            "El cantón con ID {$distrito->getIdCanton()} no existe"
        );

        $id = $this->generarSiguienteId($this->conexion, "tbdistrito", "tbdistritoid");

        $sql = "INSERT INTO tbdistrito
                (tbdistritoid, tbcantonid, tbdistritonombre, tbdistritoactivo)
                VALUES (:id, :idCanton, :nombre, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idCanton" => $distrito->getIdCanton(),
            ":nombre" => $distrito->getNombre(),
            ":activo" => $distrito->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbdistrito ORDER BY tbdistritonombre";

        $consulta = $this->conexion->query($sql);

        $distritos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $distritos[] = new Distrito(
                (int) $fila["tbcantonid"],
                $fila["tbdistritonombre"],
                (bool) $fila["tbdistritoactivo"],
                (int) $fila["tbdistritoid"]
            );
        }

        return $distritos;
    }

    public function obtenerPorId(int $idDistrito): ?Distrito
    {
        $sql = "SELECT * FROM tbdistrito WHERE tbdistritoid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idDistrito]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Distrito(
            (int) $fila["tbcantonid"],
            $fila["tbdistritonombre"],
            (bool) $fila["tbdistritoactivo"],
            (int) $fila["tbdistritoid"]
        );
    }

    public function obtenerPorCanton(int $idCanton): array
    {
        $sql = "SELECT * FROM tbdistrito
                WHERE tbcantonid = :idCanton
                AND tbdistritoactivo = 1
                ORDER BY tbdistritonombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCanton" => $idCanton]);

        $distritos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $distritos[] = new Distrito(
                (int) $fila["tbcantonid"],
                $fila["tbdistritonombre"],
                (bool) $fila["tbdistritoactivo"],
                (int) $fila["tbdistritoid"]
            );
        }

        return $distritos;
    }

    public function actualizar(Distrito $distrito): bool
    {
        $this->validarReferencia(
            $this->conexion,
            "tbcanton",
            "tbcantonid",
            $distrito->getIdCanton(),
            "El cantón con ID {$distrito->getIdCanton()} no existe"
        );

        $sql = "UPDATE tbdistrito
                SET tbcantonid = :idCanton, tbdistritonombre = :nombre, tbdistritoactivo = :activo
                WHERE tbdistritoid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idCanton" => $distrito->getIdCanton(),
            ":nombre" => $distrito->getNombre(),
            ":activo" => $distrito->isActivo(),
            ":id" => $distrito->getIdDistrito()
        ]);
    }

    public function eliminar(int $idDistrito): bool
    {
        $sql = "UPDATE tbdistrito SET tbdistritoactivo = 0 WHERE tbdistritoid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idDistrito]);
    }
}