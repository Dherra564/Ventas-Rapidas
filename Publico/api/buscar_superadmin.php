<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/SuperAdminController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $controlador = new SuperAdminController();
    $admin = $controlador->buscar($usuario['id']);

    if ($admin === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'No se encontró tu cuenta']);
        exit;
    }

    echo json_encode([
        'exito' => true,
        'admin' => [
            'idSuperAdmin' => $admin->getIdSuperAdmin(),
            'nombreCompleto' => $admin->getNombreCompleto(),
            'correo' => $admin->getCorreo()
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}