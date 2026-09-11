<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

$datos = json_decode(file_get_contents('php://input'), true);
$idProducto = (int) ($datos['idProducto'] ?? 0);

if ($idProducto <= 0) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Producto inválido']);
    exit;
}

try {
    $controlador = new ProductoController();
    $exito = $controlador->eliminar($idProducto);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Producto eliminado correctamente' : 'No se pudo eliminar el producto'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}