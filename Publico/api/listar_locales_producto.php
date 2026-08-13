<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoLocalController.php';

$idProducto = (int) ($_GET['idProducto'] ?? 0);

try {
    $controlador = new ProductoLocalController();
    echo json_encode(['exito' => true, 'locales' => $controlador->listarPorProducto($idProducto)]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}