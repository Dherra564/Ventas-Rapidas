<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteLocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $controlador = new ClienteLocalController();
    $id = $controlador->seguir(
        $usuario['id'],
        (int) ($datos['idLocal'] ?? 0)
    );

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Local agregado' : 'No se pudo agregar el local'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}