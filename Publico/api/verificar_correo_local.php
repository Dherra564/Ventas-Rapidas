<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$correo = trim($_GET['correo'] ?? '');

try {
    $controlador = new LocalController();
    echo json_encode(['existe' => $controlador->existeCorreoLocal($correo)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}