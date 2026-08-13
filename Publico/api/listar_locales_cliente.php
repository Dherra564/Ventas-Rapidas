<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteLocalController.php';

$idCliente = (int) ($_GET['idCliente'] ?? 0);

try {
    $controlador = new ClienteLocalController();
    echo json_encode(['exito' => true, 'locales' => $controlador->listarPorCliente($idCliente)]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}