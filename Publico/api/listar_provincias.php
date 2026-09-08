<?php

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Comun/LectorUbicaciones.php';

try {
    $datos = LectorUbicaciones::provincias();

    echo json_encode(['exito' => true, 'provincias' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}