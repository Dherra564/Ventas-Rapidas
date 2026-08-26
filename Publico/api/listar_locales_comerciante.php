<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

try {
    $controlador = new LocalController();
    $locales = $controlador->listarPorComerciante($usuario['id']);

    $datos = array_map(fn($local) => [
        'idLocal' => $local->getIdLocal(),
        'nombreLocal' => $local->getNombreLocal(),
        'logo' => $local->getLogo(),
        'activo' => $local->isActivo()
    ], $locales);

    echo json_encode(['exito' => true, 'locales' => $datos]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}