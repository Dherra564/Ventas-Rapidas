<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$nombre = trim($_GET['nombre'] ?? '');

try {
    $controlador = new LocalController();
    echo json_encode(['disponible' => !$controlador->existeNombreLocal($nombre)]);
} catch (Exception $e) {
    echo json_encode(['disponible' => true]);
}