<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/CarritoCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

try {
    $controlador = new CarritoCompraController();
    $carritosCompra = $controlador->listar($usuario);

    echo json_encode([
        'exito' => true,
        'carritos' => $carritosCompra,
        'totalProductos' => array_sum(array_column($carritosCompra, 'cantidadProductos'))
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'carritos' => [], 'totalProductos' => 0]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage(), 'carritos' => [], 'totalProductos' => 0]);
}