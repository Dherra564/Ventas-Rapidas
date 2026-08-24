<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ResenaController.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idCliente = (int) ($datos['idCliente'] ?? 0);
    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $comentario = trim($datos['comentario'] ?? '');
    $puntuacion = (int) ($datos['puntuacion'] ?? 0);

    $controlador = new ResenaController();
    $id = $controlador->registrar($idCliente, $idLocal, $comentario, $puntuacion);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Reseña publicada correctamente' : 'No se pudo publicar la reseña',
        'idResena' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
