<?php

require_once __DIR__ . "/../Modelos/Pedido.php";
require_once __DIR__ . "/../Modelos/PedidoPago.php";

class FormateadorPedido
{
    public static function aArreglo(array $fila, bool $incluirRetiroCodigo): array
    {
        $pedido = $fila["pedido"];

        $detalles = array_map(fn(DetallePedido $detalle) => [
            "idProducto" => $detalle->getIdProducto(),
            "productoNombre" => $detalle->getProductoNombre(),
            "cantidad" => $detalle->getCantidad(),
            "precio" => $detalle->getPrecio(),
            "descuentoPorcentaje" => $detalle->getDescuentoPorcentaje(),
            "precioUnitario" => $detalle->getPrecioUnitario(),
            "subtotal" => $detalle->getSubtotal()
        ], $pedido->getDetalles());

        return [
            "idPedido" => $pedido->getIdPedido(),
            "numero" => self::numero($pedido->getIdPedido()),
            "estado" => $pedido->getEstado(),
            "total" => $pedido->getTotal(),
            "cantidadArticulos" => $pedido->getCantidadArticulos(),
            "registroFecha" => $pedido->getRegistroFecha()?->format("Y-m-d H:i:s"),
            "actualizacionFecha" => $pedido->getActualizacionFecha()?->format("Y-m-d H:i:s"),
            "motivo" => $pedido->getMotivo(),
            "retiroCodigo" => $incluirRetiroCodigo ? $pedido->getRetiroCodigo() : null,
            "idLocal" => $pedido->getIdLocal(),
            "localNombre" => $fila["localNombre"] ?? null,
            "localLogo" => $fila["localLogo"] ?? null,
            "localTelefono" => $fila["localTelefono"] ?? null,
            "clienteNombre" => $fila["clienteNombre"] ?? null,
            "clienteCorreo" => $fila["clienteCorreo"] ?? null,
            "detalles" => $detalles,
            "pago" => self::pagoAArreglo($fila["pago"] ?? null)
        ];
    }

    public static function pagoAArreglo(?PedidoPago $pago): ?array
    {
        if ($pago === null) {
            return null;
        }

        return [
            "numeroSinpe" => $pago->getNumeroSinpe(),
            "codigo" => $pago->getCodigo(),
            "referencia" => $pago->getReferencia(),
            "comprobante" => $pago->getComprobante(),
            "nombre" => $pago->getNombre(),
            "telefono" => $pago->getTelefono(),
            "monto" => $pago->getMonto(),
            "registroFecha" => $pago->getRegistroFecha()?->format("Y-m-d H:i:s"),
            "recibo" => $pago->getRecibo(),
            "reciboFecha" => $pago->getReciboFecha()?->format("Y-m-d H:i:s")
        ];
    }

    public static function numero(int $idPedido): string
    {
        return "RV-" . str_pad((string) $idPedido, 6, "0", STR_PAD_LEFT);
    }

    public static function numeroRecibo(int $idPedido): string
    {
        return "REC-" . str_pad((string) $idPedido, 6, "0", STR_PAD_LEFT);
    }
}