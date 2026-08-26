<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';

try {
    if ($correo === '' || $password === '') {
        throw new InvalidArgumentException("Correo y contraseña son obligatorios");
    }

    $controlador = new ComercianteController();
    $comerciante = $controlador->login($correo, $password);

    Sesion::iniciarSesionUsuario(
        $comerciante->getIdComerciante(),
        Sesion::TIPO_COMERCIANTE,
        $comerciante->getNombreCompleto()
    );

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
} catch (InvalidArgumentException $e) {
    http_response_code(401);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}