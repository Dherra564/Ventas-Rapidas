<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$cedula = trim($_GET['cedula'] ?? '');

try {
    $controlador = new ComercianteController();
    echo json_encode(['existe' => $controlador->existeCedula($cedula)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}