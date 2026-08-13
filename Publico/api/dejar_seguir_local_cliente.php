<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteLocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $controlador = new ClienteLocalController();
    $exito = $controlador->dejarDeSeguir((int) ($datos['idClienteLocal'] ?? 0));

    echo json_encode(['exito' => $exito]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}