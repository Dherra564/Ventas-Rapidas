<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idPedido = (int) ($datos['idPedido'] ?? 0);
    $motivo = $datos['motivo'] ?? null;

    $controlador = new PedidoController();
    $controlador->cancelarPedido($usuario, $idPedido, $motivo);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Pedido cancelado. Los productos volvieron al inventario del local.'
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}