<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/SuperAdmin.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/HistorialCampoRepository.php";

class SuperAdminRepository
{
    use GeneradorId;

    private PDO $conexion;
    private HistorialCampoRepository $historialCorreo;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->historialCorreo = new HistorialCampoRepository("tbsuperadmincorreohistorico", "tbsuperadmincorreohistoricoid", "tbsuperadminid", $this->conexion);
    }

    public function insertar(SuperAdmin $superAdmin): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbsuperadmin", "tbsuperadminid");

        $sql = "INSERT INTO tbsuperadmin
                (tbsuperadminid, tbsuperadmincorreo, tbsuperadminpassword, tbsuperadminnombrecompleto, tbsuperadminregistrofecha, tbsuperadminactivo)
                VALUES (:id, :correo, :password, :nombre, NOW(), :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":correo" => $superAdmin->getCorreo(),
            ":password" => $superAdmin->getPasswordHash(),
            ":nombre" => $superAdmin->getNombreCompleto(),
            ":activo" => $superAdmin->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorId(int $idSuperAdmin): ?SuperAdmin
    {
        $sql = "SELECT * FROM tbsuperadmin WHERE tbsuperadminid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idSuperAdmin]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorCorreo(string $correo): ?SuperAdmin
    {
        $sql = "SELECT * FROM tbsuperadmin WHERE tbsuperadmincorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function actualizarCorreo(int $idSuperAdmin, string $correoNuevo): bool
    {
        $anterior = $this->obtenerPorId($idSuperAdmin);

        $sql = "UPDATE tbsuperadmin SET tbsuperadmincorreo = :correo WHERE tbsuperadminid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":correo" => $correoNuevo, ":id" => $idSuperAdmin]);

        if ($exito && $anterior !== null) {
            $this->historialCorreo->registrarSiCambio($idSuperAdmin, $anterior->getCorreo(), $correoNuevo);
        }

        return $exito;
    }

    public function actualizarPasswordHash(int $idSuperAdmin, string $passwordHash): bool
    {
        $sql = "UPDATE tbsuperadmin SET tbsuperadminpassword = :password WHERE tbsuperadminid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":password" => $passwordHash, ":id" => $idSuperAdmin]);
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbsuperadmin WHERE tbsuperadmincorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }

    private function mapearFila(array $fila): SuperAdmin
    {
        return new SuperAdmin(
            $fila["tbsuperadmincorreo"],
            $fila["tbsuperadminpassword"],
            $fila["tbsuperadminnombrecompleto"],
            (bool) $fila["tbsuperadminactivo"],
            (int) $fila["tbsuperadminid"],
            $fila["tbsuperadminregistrofecha"] != null
            ? new DateTime($fila["tbsuperadminregistrofecha"])
            : null
        );
    }
}