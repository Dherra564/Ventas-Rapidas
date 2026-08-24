<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ResenaController.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idResena = (int) ($datos['idResena'] ?? 0);
    if ($idResena <= 0) {
        throw new InvalidArgumentException('Reseña inválida');
    }

    $controlador = new ResenaController();
    $exito = $controlador->eliminar($idResena);
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Reseña eliminada correctamente' : 'No se pudo eliminar la reseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
