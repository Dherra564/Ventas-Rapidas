<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);
$idLocal = (int) ($datos['idLocal'] ?? 0);

try {
    $controlador = new LocalController();

    if (!$controlador->perteneceAComerciante($idLocal, $usuario['id'])) {
        http_response_code(403);
        throw new InvalidArgumentException('Ese local no pertenece a tu cuenta');
    }

    $exito = $controlador->eliminar($idLocal);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Local eliminado correctamente' : 'No se pudo eliminar el local'
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}