<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/FormateadorPedido.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $items = is_array($datos['items'] ?? null) ? $datos['items'] : [];

    $controlador = new PedidoController();
    $resultado = $controlador->crearPedido($usuario, $idLocal, $items);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Pedido generado. El local debe confirmarlo antes de que puedas retirarlo.',
        'idPedido' => $resultado['idPedido'],
        'numero' => FormateadorPedido::numero($resultado['idPedido']),
        'total' => $resultado['total']
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo generar el pedido: ' . $e->getMessage()]);
}