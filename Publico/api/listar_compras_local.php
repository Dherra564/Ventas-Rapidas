<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/RegistroCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $idLocal = (int) ($_GET['idLocal'] ?? 0);
    $fecha = trim($_GET['fecha'] ?? date('Y-m-d'));

    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Selecciona un local válido');
    }

    $comprasController = new RegistroCompraController();
    $clienteController = new ClienteController();
    $compras = $comprasController->listarPorLocalYFecha($idLocal, $fecha);

    $datos = [];
    foreach ($compras as $compra) {
        $cliente = $clienteController->buscar($compra->getIdCliente());
        $datos[] = [
            'idRegistroCompra' => $compra->getIdRegistroCompra(),
            'idCliente' => $compra->getIdCliente(),
            'nombreCliente' => $cliente?->getNombreCompleto() ?? '(cliente no encontrado)',
            'idLocal' => $compra->getIdLocal(),
            'fechaCompra' => $compra->getFechaCompra()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode(['exito' => true, 'compras' => $datos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'compras' => []]);
}
