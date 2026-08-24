<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

$correo = trim($_GET['correo'] ?? '');

try {
    $controlador = new ClienteController();
    echo json_encode(['existe' => $controlador->existeCorreo($correo)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}
