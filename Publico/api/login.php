<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/SuperAdminController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

try {
    if ($correo === '' || $password === '') {
        throw new InvalidArgumentException("Correo y contraseña son obligatorios");
    }

    $clienteControlador = new ClienteController();
    $cliente = null;

    try {
        $cliente = $clienteControlador->login($correo, $password);
    } catch (InvalidArgumentException $e) {
        $cliente = null;
    }

    if ($cliente !== null) {
        $comercianteControlador = new ComercianteController();
        $comerciante = $comercianteControlador->buscarPorIdUsuario($cliente->getIdUsuario());

        if ($comerciante !== null && $comerciante->isActivo()) {
            Sesion::iniciarSesionUsuario($comerciante->getIdComerciante(), Sesion::TIPO_COMERCIANTE, $comerciante->getNombreCompleto());
            echo json_encode([
                'exito' => true,
                'mensaje' => 'Sesión iniciada correctamente',
                'usuario' => [
                    'id' => $comerciante->getIdComerciante(),
                    'nombre' => $comerciante->getNombreCompleto(),
                    'correo' => $comerciante->getCorreo(),
                    'tipo' => Sesion::TIPO_COMERCIANTE
                ]
            ]);
            exit;
        }

        Sesion::iniciarSesionUsuario($cliente->getIdCliente(), Sesion::TIPO_CLIENTE, $cliente->getNombreCompleto());
        echo json_encode([
            'exito' => true,
            'mensaje' => 'Sesión iniciada correctamente',
            'usuario' => [
                'id' => $cliente->getIdCliente(),
                'nombre' => $cliente->getNombreCompleto(),
                'correo' => $cliente->getCorreo(),
                'tipo' => Sesion::TIPO_CLIENTE
            ]
        ]);
        exit;
    }

    $superAdminControlador = new SuperAdminController();
    $superAdmin = $superAdminControlador->login($correo, $password);

    Sesion::iniciarSesionUsuario($superAdmin->getIdSuperAdmin(), Sesion::TIPO_SUPERADMIN, $superAdmin->getNombreCompleto());
    echo json_encode([
        'exito' => true,
        'mensaje' => 'Sesión iniciada correctamente',
        'usuario' => [
            'id' => $superAdmin->getIdSuperAdmin(),
            'nombre' => $superAdmin->getNombreCompleto(),
            'tipo' => Sesion::TIPO_SUPERADMIN
        ]
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => 'Correo o contraseña incorrectos']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}