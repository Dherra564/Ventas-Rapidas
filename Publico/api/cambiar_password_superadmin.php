<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/SuperAdminController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

$datos = json_decode(file_get_contents('php://input'), true);
$passwordActual = $datos['passwordActual'] ?? '';
$passwordNueva = $datos['passwordNueva'] ?? '';

try {
    $controlador = new SuperAdminController();
    $exito = $controlador->cambiarPassword($usuario['id'], $passwordActual, $passwordNueva);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Contraseña actualizada correctamente' : 'No se pudo actualizar la contraseña'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}