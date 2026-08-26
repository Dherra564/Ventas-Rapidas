<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idResenia = (int) ($datos['idResenia'] ?? 0);
    if ($idResenia <= 0) {
        throw new InvalidArgumentException('Reseña inválida');
    }

    $controlador = new ReseniaController();
    $exito = $controlador->eliminar($idResenia);
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Reseña eliminada correctamente' : 'No se pudo eliminar la reseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}