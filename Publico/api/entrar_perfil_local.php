<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);

    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Falta el ID del local');
    }

    $controlador = new LocalController();
    $controlador->entrarPerfil($idLocal, $usuario['id']);

    echo json_encode(['exito' => true, 'mensaje' => 'Perfil activado']);
} catch (InvalidArgumentException $e) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}