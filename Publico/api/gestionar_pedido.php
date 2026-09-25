<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idPedido = (int) ($datos['idPedido'] ?? 0);
    $accion = $datos['accion'] ?? '';

    $controlador = new PedidoController();

    switch ($accion) {
        case 'confirmar':
            $controlador->confirmarPedido($usuario['id'], $idPedido);
            $mensaje = 'Pedido confirmado. El cliente ya puede ver su código de retiro.';
            break;

        case 'rechazar':
            $controlador->rechazarPedido($usuario['id'], $idPedido, $datos['motivo'] ?? null);
            $mensaje = 'Pedido rechazado. Los productos volvieron a tu inventario.';
            break;

        case 'entregar':
            $controlador->confirmarEntrega($usuario['id'], $idPedido, (string) ($datos['retiroCodigo'] ?? ''));
            $mensaje = '¡Entrega confirmada! El pedido quedó completado.';
            break;

        default:
            throw new InvalidArgumentException('Acción no válida');
    }

    echo json_encode(['exito' => true, 'mensaje' => $mensaje]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}