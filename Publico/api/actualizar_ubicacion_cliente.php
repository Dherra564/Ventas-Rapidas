<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

// Cualquier visitante puede usar su ubicación GPS (invitado, cliente, comerciante o superadmin).
// La ubicación solo se guarda en la base de datos si quien la envía es un cliente con sesión activa;
// para los demás roles simplemente se confirma que las coordenadas son válidas.
$usuario = Sesion::usuarioActual();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $latitud = isset($datos['latitud']) && is_numeric($datos['latitud']) ? (float) $datos['latitud'] : null;
    $longitud = isset($datos['longitud']) && is_numeric($datos['longitud']) ? (float) $datos['longitud'] : null;

    if ($latitud === null || $longitud === null) {
        throw new InvalidArgumentException('Faltan las coordenadas');
    }

    if ($latitud < -90 || $latitud > 90) {
        throw new InvalidArgumentException('La latitud debe estar entre -90 y 90');
    }

    if ($longitud < -180 || $longitud > 180) {
        throw new InvalidArgumentException('La longitud debe estar entre -180 y 180');
    }

    $guardada = false;

    if ($usuario !== null && $usuario['tipo'] === Sesion::TIPO_CLIENTE) {
        $controlador = new ClienteController();
        $guardada = $controlador->actualizarUbicacionGPS($usuario['id'], $latitud, $longitud);
    }

    echo json_encode([
        'exito' => true,
        'mensaje' => $guardada ? 'Ubicación actualizada' : 'Ubicación obtenida',
        'guardada' => $guardada
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}