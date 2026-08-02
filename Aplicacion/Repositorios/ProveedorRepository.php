<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Proveedor.php";

class ProveedorRepository
{
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(Proveedor $proveedor): bool
    {
        $sql = "INSERT INTO tbproveedor
                (
                    tbproveedornombre,
                    tbproveedorapellido,
                    tbproveedorcedula,
                    tbproveedorcorreo,
                    tbproveedorpassword,
                    tbproveedoractivo
                )
                VALUES
                (
                    :nombre,
                    :apellido,
                    :cedula,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $proveedor->getNombre(),
            ":apellido" => $proveedor->getApellido(),
            ":cedula" => $proveedor->getCedula(),
            ":correo" => $proveedor->getCorreo(),
            ":password" => $proveedor->getPasswordHash(),
            ":activo" => $proveedor->isActivo()
        ]);
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbproveedor ORDER BY tbproveedornombre";

        $consulta = $this->conexion->query($sql);

        $proveedores = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {

            $proveedores[] = new Proveedor(
                $fila["tbproveedornombre"],
                $fila["tbproveedorapellido"],
                $fila["tbproveedorcedula"],
                $fila["tbproveedorcorreo"],
                $fila["tbproveedorpassword"],
                (bool) $fila["tbproveedoractivo"],
                (int) $fila["tbproveedorid"],
                $fila["tbproveedorfecharegistro"] != null
                ? new DateTime($fila["tbproveedorfecharegistro"])
                : null
            );
        }

        return $proveedores;
    }

    public function obtenerPorId(int $idProveedor): ?Proveedor
    {
        $sql = "SELECT *
                FROM tbproveedor
                WHERE tbproveedorid = :id";

        $consulta = $this->conexion->prepare($sql);

        $consulta->execute([
            ":id" => $idProveedor
        ]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Proveedor(
            $fila["tbproveedornombre"],
            $fila["tbproveedorapellido"],
            $fila["tbproveedorcedula"],
            $fila["tbproveedorcorreo"],
            $fila["tbproveedorpassword"],
            (bool) $fila["tbproveedoractivo"],
            (int) $fila["tbproveedorid"],
            $fila["tbproveedorfecharegistro"] != null
            ? new DateTime($fila["tbproveedorfecharegistro"])
            : null
        );
    }

    public function actualizar(Proveedor $proveedor): bool
    {
        $sql = "UPDATE tbproveedor
                SET
                    tbproveedornombre = :nombre,
                    tbproveedorapellido = :apellido,
                    tbproveedorcorreo = :correo,
                    tbproveedorpassword = :password,
                    tbproveedoractivo = :activo
                WHERE tbproveedorid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $proveedor->getNombre(),
            ":apellido" => $proveedor->getApellido(),
            ":correo" => $proveedor->getCorreo(),
            ":password" => $proveedor->getPasswordHash(),
            ":activo" => $proveedor->isActivo(),
            ":id" => $proveedor->getIdProveedor()
        ]);
    }

    public function eliminar(int $idProveedor): bool
    {
        $sql = "DELETE
                FROM tbproveedor
                WHERE tbproveedorid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":id" => $idProveedor
        ]);
    }
}