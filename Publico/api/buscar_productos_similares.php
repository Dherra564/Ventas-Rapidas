<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion();

try {
    $nombre = trim($_GET['nombre'] ?? '');
    $idProductoExcluir = isset($_GET['idProductoExcluir']) && $_GET['idProductoExcluir'] !== ''
        ? (int) $_GET['idProductoExcluir']
        : null;

    $controlador = new ProductoController();
    $similares = $controlador->buscarSimilares($nombre, $idProductoExcluir);

    echo json_encode(['exito' => true, 'similares' => $similares]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}