<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idResenia = (int) ($datos['idResenia'] ?? 0);
    if ($idResenia <= 0) {
        throw new InvalidArgumentException('Reseña inválida');
    }

    $controlador = new ReseniaController();
    $actual = $controlador->buscar($idResenia);

    if ($actual === null) {
        throw new InvalidArgumentException('Reseña no encontrada');
    }

    if ($actual->getIdCliente() !== $usuario['id']) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Esa reseña no te pertenece']);
        exit;
    }

    $exito = $controlador->eliminar($idResenia);
    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Reseña eliminada correctamente' : 'No se pudo eliminar la reseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}