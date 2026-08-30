<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $idLocal = (int) ($_GET['idLocal'] ?? 0);

    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Falta el ID del local');
    }

    $controlador = new LocalController();
    $historial = $controlador->obtenerHistorialActividad($idLocal);

    $datos = array_map(fn($h) => [
        'idHistorialActividadSesionLocal' => $h->getIdHistorialActividadSesionLocal(),
        'idUsuario' => $h->getIdUsuario(),
        'tipoUsuario' => $h->getTipoUsuario(),
        'tipo' => $h->getTipo(),
        'fecha' => $h->getFecha()?->format('Y-m-d H:i:s')
    ], $historial);

    echo json_encode([
        'exito' => true,
        'historial' => $datos,
        'activoPorActividad' => $controlador->estaActivoPorActividad($idLocal)
    ]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}