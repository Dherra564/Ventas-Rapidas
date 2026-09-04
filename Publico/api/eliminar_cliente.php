<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true);
$idCliente = (int) ($datos['idCliente'] ?? 0);

if ($idCliente !== $usuario['id']) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No tienes permiso para desactivar esa cuenta']);
    exit;
}

try {
    $controlador = new ClienteController();
    $exito = $controlador->eliminar($idCliente);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Cliente desactivado correctamente' : 'No se pudo desactivar el cliente'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}