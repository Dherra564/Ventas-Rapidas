<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/SuperAdminController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

$datos = json_decode(file_get_contents('php://input'), true);
$nombreCompleto = trim($datos['nombreCompleto'] ?? '');
$correo = trim($datos['correo'] ?? '');

try {
    if ($nombreCompleto === '' || $correo === '') {
        throw new InvalidArgumentException('El nombre y el correo son obligatorios');
    }

    $controlador = new SuperAdminController();

    $adminActual = $controlador->buscar($usuario['id']);
    if ($adminActual === null) {
        throw new InvalidArgumentException('No se encontró tu cuenta');
    }

    if ($correo !== $adminActual->getCorreo() && $controlador->existeCorreo($correo)) {
        throw new InvalidArgumentException('Ese correo ya está en uso');
    }

    $exitoNombre = $controlador->cambiarNombre($usuario['id'], $nombreCompleto);
    $exitoCorreo = $controlador->cambiarCorreo($usuario['id'], $correo);

    echo json_encode([
        'exito' => $exitoNombre && $exitoCorreo,
        'mensaje' => ($exitoNombre && $exitoCorreo) ? 'Perfil actualizado correctamente' : 'No se pudo actualizar el perfil'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}