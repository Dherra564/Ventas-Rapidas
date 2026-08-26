<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Resenia.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idResenia = (int) ($datos['idResenia'] ?? 0);
    $controlador = new ReseniaController();
    $actual = $controlador->buscar($idResenia);

    if ($actual === null) {
        throw new InvalidArgumentException('Reseña no encontrada');
    }

    $resenia = new Resenia(
        $actual->getIdCliente(),
        $actual->getIdLocal(),
        trim($datos['comentario'] ?? ''),
        (int) ($datos['puntuacion'] ?? 0),
        true,
        $idResenia,
        $actual->getFechaResenia()
    );

    $exito = $controlador->editar($resenia);
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Reseña actualizada correctamente' : 'No se pudo actualizar la reseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
