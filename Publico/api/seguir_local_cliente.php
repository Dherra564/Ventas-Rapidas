<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteLocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $controlador = new ClienteLocalController();
    $id = $controlador->seguir(
        (int) ($datos['idCliente'] ?? 0),
        (int) ($datos['idLocal'] ?? 0)
    );

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Local agregado' : 'No se pudo agregar el local'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}