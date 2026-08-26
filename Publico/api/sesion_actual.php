<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::usuarioActual();

if ($usuario === null) {
    echo json_encode(['exito' => true, 'autenticado' => false]);
} else {
    echo json_encode(['exito' => true, 'autenticado' => true, 'usuario' => $usuario]);
}