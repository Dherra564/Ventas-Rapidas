<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$numeroIdentificacion = trim($_GET['numeroIdentificacion'] ?? '');

try {
    $controlador = new ComercianteController();
    echo json_encode(['existe' => $controlador->existeIdentificacion($numeroIdentificacion)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}