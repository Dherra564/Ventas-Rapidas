<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/ClienteLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";

class ClienteLocalRepository
{
    use GeneradorId, ValidadorReferencia;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function insertar(ClienteLocal $clienteLocal): int|false
    {
        $this->validarReferencia(
            $this->conexion,
            "tbcliente",
            "tbclienteid",
            $clienteLocal->getIdCliente(),
            "El cliente con ID {$clienteLocal->getIdCliente()} no existe"
        );

        $this->validarReferencia(
            $this->conexion,
            "tblocal",
            "tblocalid",
            $clienteLocal->getIdLocal(),
            "El local con ID {$clienteLocal->getIdLocal()} no existe"
        );

        $id = $this->generarSiguienteId($this->conexion, "tbclientelocal", "tbclientelocalid");

        $sql = "INSERT INTO tbclientelocal
                (tbclientelocalid, tbclienteid, tblocalid, tbclientelocalactivo)
                VALUES (:id, :idCliente, :idLocal, :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idCliente" => $clienteLocal->getIdCliente(),
            ":idLocal" => $clienteLocal->getIdLocal(),
            ":activo" => $clienteLocal->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerLocalesPorCliente(int $idCliente): array
    {
        $sql = "SELECT tblocalid
                FROM tbclientelocal
                WHERE tbclienteid = :idCliente
                AND tbclientelocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    public function obtenerRelacionesPorCliente(int $idCliente): array
    {
        $sql = "SELECT tbclientelocalid, tblocalid
                FROM tbclientelocal
                WHERE tbclienteid = :idCliente
                AND tbclientelocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerClientesPorLocal(int $idLocal): array
    {
        $sql = "SELECT tbclienteid
                FROM tbclientelocal
                WHERE tblocalid = :idLocal
                AND tbclientelocalactivo = 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    public function eliminar(int $idClienteLocal): bool
    {
        $sql = "UPDATE tbclientelocal SET tbclientelocalactivo = 0 WHERE tbclientelocalid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idClienteLocal]);
    }
}