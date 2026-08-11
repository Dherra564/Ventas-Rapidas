<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

$numeroIdentificacion = trim($_GET['numeroIdentificacion'] ?? '');

try {
    $controlador = new ClienteController();
    echo json_encode(['existe' => $controlador->existeIdentificacion($numeroIdentificacion)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}