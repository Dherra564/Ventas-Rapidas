<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);

    $controlador = new PedidoController();
    $pago = $controlador->iniciarPagoSinpe($usuario, $idLocal);

    echo json_encode(['exito' => true, 'pago' => $pago]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}