<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

// Nota: reactivar cuentas ajenas requiere un rol de administrador, que todavía
// no existe en Sesion. Mientras tanto solo se permite operar sobre la propia
// cuenta autenticada, para no dejar este endpoint abierto a cualquiera.
$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);
$idComerciante = (int) ($datos['idComerciante'] ?? 0);

if ($idComerciante !== $usuario['id']) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tienes permiso para activar esa cuenta']);
    exit;
}

try {
    $controlador = new ComercianteController();
    $exito = $controlador->activar($idComerciante);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Comerciante activado correctamente' : 'No se pudo activar el comerciante'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}