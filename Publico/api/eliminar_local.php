<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

$datos = json_decode(file_get_contents('php://input'), true);
$idLocal = (int) ($datos['idLocal'] ?? 0);

if ($idLocal <= 0) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => 'Local inválido']);
    exit;
}

try {
    $controlador = new LocalController();
    $exito = $controlador->eliminar($idLocal);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Local eliminado correctamente' : 'No se pudo eliminar el local'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}