<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Comun/LectorUbicaciones.php';

$idCanton = (int)($_GET['idCanton'] ?? 0);

try {
    $datos = LectorUbicaciones::distritosPorCanton($idCanton);

    echo json_encode(['exito' => true, 'distritos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}