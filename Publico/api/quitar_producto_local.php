<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoLocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $controlador = new ProductoLocalController();
    $exito = $controlador->quitar((int) ($datos['idProductoLocal'] ?? 0));

    echo json_encode(['exito' => $exito]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}