<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion();

try {
    $nombre = trim($_GET['nombre'] ?? '');
    $idLocalExcluir = isset($_GET['idLocalExcluir']) && $_GET['idLocalExcluir'] !== ''
        ? (int) $_GET['idLocalExcluir']
        : null;

    $controlador = new LocalController();
    $similares = $controlador->buscarSimilares($nombre, $idLocalExcluir);

    echo json_encode(['exito' => true, 'similares' => $similares]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}