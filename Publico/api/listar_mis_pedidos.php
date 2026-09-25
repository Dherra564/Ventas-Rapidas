<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/FormateadorPedido.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

try {
    $controlador = new PedidoController();
    $pedidos = $controlador->listarPedidosDeCliente($usuario);

    echo json_encode([
        'exito' => true,
        'pedidos' => array_map(fn($fila) => FormateadorPedido::aArreglo($fila, true), $pedidos)
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'pedidos' => []]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage(), 'pedidos' => []]);
}