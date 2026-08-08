<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Comerciante.php";

class ComercianteRepository
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Comerciante $comerciante): bool
    {
        $sql = "INSERT INTO tbcomerciante
                (
                    tbcomerciantenombre,
                    tbcomerciantealias,
                    tbcomerciantecedula,
                    tbcomerciantecorreo,
                    tbcomerciantepassword,
                    tbcomercianteactivo
                )
                VALUES
                (
                    :nombre,
                    :alias,
                    :cedula,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $comerciante->getNombreCompleto(),
            ":alias" => $comerciante->getAlias(),
            ":cedula" => $comerciante->getCedula(),
            ":correo" => $comerciante->getCorreo(),
            ":password" => $comerciante->getPasswordHash(),
            ":activo" => $comerciante->isActivo()
        ]);
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcomerciante ORDER BY tbcomerciantenombre";

        $consulta = $this->conexion->query($sql);

        $comerciantes = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $comerciantes[] = new Comerciante(
                $fila["tbcomerciantenombre"],
                $fila["tbcomerciantealias"],
                $fila["tbcomerciantecedula"],
                $fila["tbcomerciantecorreo"],
                $fila["tbcomerciantepassword"],
                (bool) $fila["tbcomercianteactivo"],
                (int) $fila["tbcomercianteid"],
                $fila["tbcomerciantefecharegistroportal"] != null
                ? new DateTime($fila["tbcomerciantefecharegistroportal"])
: null
            );
        }

        return $comerciantes;
    }

    public function obtenerPorId(int $idComerciante): ?Comerciante
    {
        $sql = "SELECT *
                FROM tbcomerciante
                WHERE tbcomercianteid = :id";
            
        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id" => $idComerciante
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Comerciante(
            $fila["tbcomerciantenombre"],
            $fila["tbcomerciantealias"],
            $fila["tbcomerciantecedula"],
            $fila["tbcomerciantecorreo"],
            $fila["tbcomerciantepassword"],
            (bool) $fila["tbcomercianteactivo"],
            (int) $fila["tbcomercianteid"],
            $fila["tbcomerciantefecharegistroportal"] != null
            ? new DateTime($fila["tbcomerciantefecharegistroportal"])
            : null
        );
    }

    public function actualizar(Comerciante $comerciante): bool
    {
        $sql = "UPDATE tbcomerciante
                SET
                    tbcomerciantenombre = :nombre,
                    tbcomerciantealias = :alias,
                    tbcomerciantecorreo = :correo,
                    tbcomerciantepassword = :password,
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
        $sql = "DELETE
                FROM tbcomerciante
                WHERE tbcomercianteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":id" => $idComerciante
        ]);
    }
}