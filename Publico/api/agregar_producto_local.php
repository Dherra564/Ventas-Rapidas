<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoLocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $controlador = new ProductoLocalController();
    $id = $controlador->agregar(
        (int) ($datos['idProducto'] ?? 0),
        (int) ($datos['idLocal'] ?? 0)
    );

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Local agregado al producto' : 'No se pudo agregar el local'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}