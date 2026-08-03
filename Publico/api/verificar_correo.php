<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProveedorController.php';

$correo = trim($_GET['correo'] ?? '');

try {
    $controlador = new ProveedorController();
    echo json_encode(['existe' => $controlador->existeCorreo($correo)]);
} catch (Exception $e) {
    echo json_encode(['existe' => false]);
}