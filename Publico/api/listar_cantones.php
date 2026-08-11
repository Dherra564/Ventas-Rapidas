<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/CantonController.php';

$idProvincia = (int)($_GET['idProvincia'] ?? 0);

try {
    $controlador = new CantonController();
    $cantones = $controlador->listarPorProvincia($idProvincia);

    $datos = array_map(fn($c) => [
        'idCanton' => $c->getIdCanton(),
        'nombre' => $c->getNombre()
    ], $cantones);

    echo json_encode(['exito' => true, 'cantones' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}