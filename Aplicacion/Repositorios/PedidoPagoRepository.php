<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/PedidoPago.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class PedidoPagoRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function insertar(PedidoPago $pago): int
    {
        $idPedidoPago = $this->generarSiguienteId($this->conexion, "tbpedidopago", "tbpedidopagoid");

        $sql = "INSERT INTO tbpedidopago
                (
                    tbpedidopagoid,
                    tbpedidoid,
                    tbpedidopagonumerosinpe,
                    tbpedidopagocodigo,
                    tbpedidopagoreferencia,
                    tbpedidopagocomprobante,
                    tbpedidopagonombre,
                    tbpedidopagotelefono,
                    tbpedidopagomonto,
                    tbpedidopagoregistrofecha,
                    tbpedidopagorecibo,
                    tbpedidopagorecibofecha,
                    tbpedidopagoactivo
                )
                VALUES
                (
                    :id,
                    :idPedido,
                    :numeroSinpe,
                    :codigo,
                    :referencia,
                    :comprobante,
                    :nombre,
                    :telefono,
                    :monto,
                    NOW(),
                    NULL,
                    NULL,
                    1
                )";

        $this->conexion->prepare($sql)->execute([
            ":id" => $idPedidoPago,
            ":idPedido" => $pago->getIdPedido(),
            ":numeroSinpe" => $pago->getNumeroSinpe(),
            ":codigo" => $pago->getCodigo(),
            ":referencia" => $pago->getReferencia(),
            ":comprobante" => $pago->getComprobante(),
            ":nombre" => $pago->getNombre(),
            ":telefono" => $pago->getTelefono(),
            ":monto" => $pago->getMonto()
        ]);

        return $idPedidoPago;
    }

    public function obtenerPorPedido(int $idPedido): ?PedidoPago
    {
        return $this->obtenerPorPedidos([$idPedido])[$idPedido] ?? null;
    }

    public function obtenerPorPedidos(array $idsPedidos): array
    {
        $idsPedidos = array_values(array_filter(array_map('intval', $idsPedidos), fn($id) => $id > 0));

        if (empty($idsPedidos)) {
            return [];
        }

        $marcadores = implode(",", array_fill(0, count($idsPedidos), "?"));
        $sql = "SELECT * FROM tbpedidopago
                WHERE tbpedidoid IN ($marcadores)
                  AND tbpedidopagoactivo = 1
                ORDER BY tbpedidopagoid";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($idsPedidos);

        $pagos = [];
        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $pagos[(int) $fila["tbpedidoid"]] = $this->mapearFila($fila);
        }
        return $pagos;
    }

    public function generarRecibo(int $idPedido, string $numeroRecibo): bool
    {
        $sql = "UPDATE tbpedidopago
                SET tbpedidopagorecibo = :recibo,
                    tbpedidopagorecibofecha = NOW()
                WHERE tbpedidoid = :idPedido
                  AND tbpedidopagoactivo = 1
                  AND tbpedidopagorecibo IS NULL";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":recibo" => $numeroRecibo, ":idPedido" => $idPedido]);
        return $consulta->rowCount() > 0;
    }

    public function ultimoTelefonoDeCliente(int $idCliente): ?string
    {
        $sql = "SELECT pp.tbpedidopagotelefono
                FROM tbpedidopago pp
                INNER JOIN tbpedido p ON p.tbpedidoid = pp.tbpedidoid
                WHERE p.tbclienteid = :idCliente
                  AND pp.tbpedidopagoactivo = 1
                ORDER BY pp.tbpedidopagoid DESC
                LIMIT 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);
        $telefono = $consulta->fetchColumn();
        return $telefono !== false ? $telefono : null;
    }

    private function mapearFila(array $fila): PedidoPago
    {
        return new PedidoPago(
            $fila["tbpedidopagonombre"],
            $fila["tbpedidopagotelefono"],
            $fila["tbpedidopagocodigo"],
            $fila["tbpedidopagoreferencia"],
            $fila["tbpedidopagocomprobante"],
            $fila["tbpedidopagonumerosinpe"],
            (float) $fila["tbpedidopagomonto"],
            (int) $fila["tbpedidoid"],
            (int) $fila["tbpedidopagoid"],
            $fila["tbpedidopagoregistrofecha"] ? new DateTime($fila["tbpedidopagoregistrofecha"]) : null,
            $fila["tbpedidopagorecibo"],
            $fila["tbpedidopagorecibofecha"] ? new DateTime($fila["tbpedidopagorecibofecha"]) : null,
            (bool) $fila["tbpedidopagoactivo"]
        );
    }
}