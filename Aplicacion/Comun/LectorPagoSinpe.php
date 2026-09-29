<?php

require_once __DIR__ . "/ManejadorImagenes.php";
require_once __DIR__ . "/../Modelos/PedidoPago.php";

trait LectorPagoSinpe
{
    use ManejadorImagenes;

    protected function leerPagoSinpe(): PedidoPago
    {
        $archivo = $_FILES['comprobante'] ?? null;

        if ($archivo === null || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            throw new InvalidArgumentException("Sube el comprobante del SINPE");
        }

        $pago = new PedidoPago(
            $_POST['nombre'] ?? '',
            $_POST['telefono'] ?? '',
            $_POST['codigo'] ?? '',
            $_POST['referencia'] ?? '',
            'pendiente'
        );

        $nombreComprobante = $this->subirImagenPerfil($archivo, 'comprobante');
        $pago->setComprobante($nombreComprobante);

        return $pago;
    }

    protected function descartarComprobante(?PedidoPago $pago): void
    {
        if ($pago !== null && $pago->getComprobante() !== 'pendiente') {
            $this->eliminarImagen($pago->getComprobante());
        }
    }
}