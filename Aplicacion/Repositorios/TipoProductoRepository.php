<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/TipoProducto.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class TipoProductoRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
    }

    public function insertar(TipoProducto $tipoProducto): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbproductotipo", "tbproductotipoid");

        $sql = "INSERT INTO tbproductotipo (tbproductotipoid, tbproductotiponombre)
                VALUES (:id, :nombre)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $tipoProducto->getNombre()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbproductotipo ORDER BY tbproductotiponombre";

        $consulta = $this->conexion->query($sql);

        $tipos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $tipos[] = new TipoProducto(
                $fila["tbproductotiponombre"],
                (int) $fila["tbproductotipoid"]
            );
        }

        return $tipos;
    }

    public function buscarPorNombre(string $textoParcial): array
    {
        $sql = "SELECT * FROM tbproductotipo
                WHERE tbproductotiponombre LIKE :texto
                ORDER BY tbproductotiponombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":texto" => "%{$textoParcial}%"]);

        $tipos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $tipos[] = new TipoProducto(
                $fila["tbproductotiponombre"],
                (int) $fila["tbproductotipoid"]
            );
        }

        return $tipos;
    }

    public function obtenerPorNombreExacto(string $nombre): ?TipoProducto
    {
        $sql = "SELECT * FROM tbproductotipo WHERE tbproductotiponombre = :nombre LIMIT 1";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":nombre" => $nombre]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new TipoProducto(
            $fila["tbproductotiponombre"],
            (int) $fila["tbproductotipoid"]
        );
    }

    public function obtenerPorId(int $idTipoProducto): ?TipoProducto
    {
        $sql = "SELECT * FROM tbproductotipo WHERE tbproductotipoid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idTipoProducto]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new TipoProducto(
            $fila["tbproductotiponombre"],
            (int) $fila["tbproductotipoid"]
        );
    }

}