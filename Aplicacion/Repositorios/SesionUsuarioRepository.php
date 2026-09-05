<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/SesionUsuario.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class SesionUsuarioRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrarLogin(int $idUsuario, string $tipoUsuario): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbsesion", "tbsesionid");

        $sql = "INSERT INTO tbsesion
                (tbsesionid, tbsesionusuarioid, tbsesionusuariotipo, tbsesionfechainicio, tbsesionactivo)
                VALUES (:id, :idUsuario, :tipoUsuario, NOW(), 1)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUsuario" => $idUsuario,
            ":tipoUsuario" => $tipoUsuario
        ]);

        return $exito ? $id : false;
    }

    public function cerrarSesion(int $idSesion): bool
    {
        $sql = "UPDATE tbsesion
                SET tbsesionfechacierre = NOW(), tbsesionactivo = 0
                WHERE tbsesionid = :id";

        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":id" => $idSesion]);
    }
}