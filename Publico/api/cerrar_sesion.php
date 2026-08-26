<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::cerrar();

echo json_encode(['exito' => true, 'mensaje' => 'Sesión cerrada']);