<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Provincia.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ProvinciaRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Provincia $provincia): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbprovincia", "tbprovinciaid");

        $sql = "INSERT INTO tbprovincia
                (tbprovinciaid, tbprovincianombre, tbprovinciaactivo)
                VALUES (:id, :nombre, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $provincia->getNombre(),
            ":activo" => $provincia->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbprovincia ORDER BY tbprovincianombre";

        $consulta = $this->conexion->query($sql);

        $provincias = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $provincias[] = new Provincia(
                $fila["tbprovincianombre"],
                (bool) $fila["tbprovinciaactivo"],
                (int) $fila["tbprovinciaid"]
            );
        }

        return $provincias;
    }

    public function obtenerPorId(int $idProvincia): ?Provincia
    {
        $sql = "SELECT * FROM tbprovincia WHERE tbprovinciaid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idProvincia]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Provincia(
            $fila["tbprovincianombre"],
            (bool) $fila["tbprovinciaactivo"],
            (int) $fila["tbprovinciaid"]
        );
    }

    public function actualizar(Provincia $provincia): bool
    {
        $sql = "UPDATE tbprovincia
                SET tbprovincianombre = :nombre, tbprovinciaactivo = :activo
                WHERE tbprovinciaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $provincia->getNombre(),
            ":activo" => $provincia->isActivo(),
            ":id" => $provincia->getIdProvincia()
        ]);
    }

    public function eliminar(int $idProvincia): bool
    {
        $sql = "UPDATE tbprovincia SET tbprovinciaactivo = 0 WHERE tbprovinciaid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idProvincia]);
    }
}