<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

$datos = json_decode(file_get_contents('php://input'), true);
$idCliente = (int) ($datos['idCliente'] ?? 0);

try {
    $controlador = new ClienteController();
    $exito = $controlador->eliminar($idCliente);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Cliente desactivado correctamente' : 'No se pudo desactivar el cliente'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}