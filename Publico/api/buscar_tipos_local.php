<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$texto = trim($_GET['texto'] ?? '');

try {
    $controlador = new LocalController();
    $tipos = $texto !== '' ? $controlador->buscarTiposCoincidentes($texto) : [];

    $datos = array_map(fn($t) => [
        'idTipoLocal' => $t->getIdTipoLocal(),
        'nombre' => $t->getNombre()
    ], $tipos);

    echo json_encode(['exito' => true, 'tipos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'tipos' => []]);
}