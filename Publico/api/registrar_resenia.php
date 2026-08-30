<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $comentario = trim($datos['comentario'] ?? '');
    $puntuacion = (int) ($datos['puntuacion'] ?? 0);

    $controlador = new ReseniaController();
    $id = $controlador->registrar($usuario['id'], $idLocal, $comentario, $puntuacion);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Reseña publicada correctamente' : 'No se pudo publicar la reseña',
        'idResenia' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}