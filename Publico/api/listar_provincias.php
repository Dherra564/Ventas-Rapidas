<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProvinciaController.php';

try {
    $controlador = new ProvinciaController();
    $provincias = $controlador->listar();

    $datos = array_map(fn($p) => [
        'idProvincia' => $p->getIdProvincia(),
        'nombre' => $p->getNombre()
    ], $provincias);

    echo json_encode(['exito' => true, 'provincias' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}