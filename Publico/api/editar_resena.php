<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ResenaController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Resena.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idResena = (int) ($datos['idResena'] ?? 0);
    $controlador = new ResenaController();
    $actual = $controlador->buscar($idResena);

    if ($actual === null) {
        throw new InvalidArgumentException('Reseña no encontrada');
    }

    $resena = new Resena(
        $actual->getIdCliente(),
        $actual->getIdLocal(),
        trim($datos['comentario'] ?? ''),
        (int) ($datos['puntuacion'] ?? 0),
        true,
        $idResena,
        $actual->getFechaResena()
    );

    $exito = $controlador->editar($resena);
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Reseña actualizada correctamente' : 'No se pudo actualizar la reseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
