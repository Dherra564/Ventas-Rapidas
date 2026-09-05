<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);
$idComerciante = (int) ($datos['idComerciante'] ?? 0);

if ($idComerciante !== $usuario['id']) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tienes permiso para desactivar esa cuenta']);
    exit;
}

try {
    $controlador = new ComercianteController();
    $exito = $controlador->eliminar($idComerciante);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Comerciante desactivado correctamente' : 'No se pudo desactivar el comerciante'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}