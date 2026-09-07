<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/SuperAdminController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

try {
    if ($correo === '' || $password === '') {
        throw new InvalidArgumentException("Correo y contraseña son obligatorios");
    }

    $controlador = new SuperAdminController();
    $superAdmin = $controlador->login($correo, $password);

    Sesion::iniciarSesionUsuario(
        $superAdmin->getIdSuperAdmin(),
        'SuperAdmin',
        $superAdmin->getNombreCompleto()
    );

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Sesión iniciada correctamente',
        'usuario' => [
            'id' => $superAdmin->getIdSuperAdmin(),
            'nombre' => $superAdmin->getNombreCompleto(),
            'tipo' => 'SuperAdmin'
        ]
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}