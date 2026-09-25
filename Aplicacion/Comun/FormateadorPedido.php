<?php

require_once __DIR__ . "/../Modelos/Pedido.php";

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
            "detalles" => $detalles
        ];
    }

    public static function numero(int $idPedido): string
    {
        return "RV-" . str_pad((string) $idPedido, 6, "0", STR_PAD_LEFT);
    }
}