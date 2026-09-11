<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/HistorialController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $idProducto = (int) ($_GET['idProducto'] ?? 0);

    if ($idProducto <= 0) {
        throw new InvalidArgumentException('Producto inválido');
    }

    $controlador = new HistorialController();
    $datos = [];

    foreach (['precio', 'descuento'] as $campo) {
        $registros = $controlador->listarHistorial('Producto', $campo, $idProducto);
        $datos[$campo] = array_map(fn($h) => [
            'valorAnterior' => $h->getValorAnterior(),
            'valorNuevo' => $h->getValorNuevo(),
            'fecha' => $h->getFecha()?->format('Y-m-d H:i:s')
        ], $registros);
    }

    echo json_encode(['exito' => true, 'campos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}