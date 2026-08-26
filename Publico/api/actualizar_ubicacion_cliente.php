<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

// Solo el cliente con sesión activa puede actualizar su propia ubicación.
$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $latitud = isset($datos['latitud']) ? (float) $datos['latitud'] : null;
    $longitud = isset($datos['longitud']) ? (float) $datos['longitud'] : null;

    if ($latitud === null || $longitud === null) {
        throw new InvalidArgumentException('Faltan las coordenadas');
    }

    $controlador = new ClienteController();
    $controlador->actualizarUbicacionGPS($usuario['id'], $latitud, $longitud);

    echo json_encode(['exito' => true, 'mensaje' => 'Ubicación actualizada']);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}