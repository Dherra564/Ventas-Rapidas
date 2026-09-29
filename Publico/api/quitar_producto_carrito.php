<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/CarritoCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $idProducto = (int) ($datos['idProducto'] ?? 0);

    $controlador = new CarritoCompraController();
    $totalProductos = $controlador->quitarProducto($usuario, $idLocal, $idProducto);

    echo json_encode(['exito' => true, 'mensaje' => 'Producto quitado del carrito', 'totalProductos' => $totalProductos]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}