<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Comun/LectorUbicaciones.php';

$idProvincia = (int)($_GET['idProvincia'] ?? 0);

try {
    $datos = LectorUbicaciones::cantonesPorProvincia($idProvincia);

    echo json_encode(['exito' => true, 'cantones' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}