<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ComercianteRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Comerciante $comerciante): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbcomerciante", "tbcomercianteid");

        $sql = "INSERT INTO tbcomerciante
                (
                    tbcomercianteid,
                    tbcomeriantenombre,
                    tbcomerciantealias,
                    tbcomerciantecedula,
                    tbcomerciantecorreo,
                    tbcomeriantepassword,
                    tbcomercianteactivo
                )
                VALUES
                (
                    :id,
                    :nombre,
                    :alias,
                    :cedula,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":cedula" => $comerciante->getCedula(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcomerciante ORDER BY tbcomeriantenombre";

        $consulta = $this->conexion->query($sql);

        $comerciantes = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $comerciantes[] = new Comerciante(
                $fila["tbcomeriantenombre"],
                $fila["tbcomerciantealias"],
                $fila["tbcomerciantecedula"],
                $fila["tbcomerciantecorreo"],
                $fila["tbcomeriantepassword"],
                (bool) $fila["tbcomercianteactivo"],
                (int) $fila["tbcomercianteid"],
                $fila["tbcomeriantefecharegistroportal"] != null
                ? new DateTime($fila["tbcomeriantefecharegistroportal"])
                : null
            );
        }

        return $comerciantes;
    }

    public function obtenerPorId(int $idComerciante): ?Comerciante
    {
        $sql = "SELECT * FROM tbcomerciante WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idComerciante]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Comerciante(
            $fila["tbcomeriantenombre"],
            $fila["tbcomerciantealias"],
            $fila["tbcomerciantecedula"],
            $fila["tbcomerciantecorreo"],
            $fila["tbcomeriantepassword"],
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomeriantefecharegistroportal"] != null
            ? new DateTime($fila["tbcomeriantefecharegistroportal"])
            : null
        );
    }

    public function actualizar(Comerciante $comerciante): bool
    {
        $sql = "UPDATE tbcomerciante
                SET
                    tbcomeriantenombre = :nombre,
                    tbcomerciantealias = :alias,
                    tbcomerciantecorreo = :correo,
                    tbcomeriantepassword = :password,
                    tbcomercianteactivo = :activo
                WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo(),
            ":id" => $comerciante->getIdComerciante()
        ]);
    }

    public function eliminar(int $idComerciante): bool
    {
        $sql = "DELETE FROM tbcomerciante WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idComerciante]);
    }
}