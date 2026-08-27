<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/UbicacionController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $latitud = isset($datos['latitud']) ? (float) $datos['latitud'] : null;
    $longitud = isset($datos['longitud']) ? (float) $datos['longitud'] : null;

    if ($latitud === null || $longitud === null) {
        throw new InvalidArgumentException('Faltan las coordenadas de ubicación');
    }

    $controlador = new UbicacionController();
    $controlador->registrarUbicacionLogin($usuario['id'], $usuario['tipo'], $latitud, $longitud);

    echo json_encode(['exito' => true]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}