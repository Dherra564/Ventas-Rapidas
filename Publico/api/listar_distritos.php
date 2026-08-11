<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/DistritoController.php';

$idCanton = (int)($_GET['idCanton'] ?? 0);

try {
    $controlador = new DistritoController();
    $distritos = $controlador->listarPorCanton($idCanton);

    $datos = array_map(fn($d) => [
        'idDistrito' => $d->getIdDistrito(),
        'nombre' => $d->getNombre()
    ], $distritos);

    echo json_encode(['exito' => true, 'distritos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}