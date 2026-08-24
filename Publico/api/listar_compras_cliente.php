<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/RegistroCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $idCliente = (int) ($_GET['idCliente'] ?? 0);
    $fecha = trim($_GET['fecha'] ?? '');

    if ($idCliente <= 0) {
        throw new InvalidArgumentException('Selecciona un cliente válido');
    }

    $comprasController = new RegistroCompraController();
    $localController = new LocalController();

    $compras = $fecha !== ''
        ? $comprasController->listarPorClienteYFecha($idCliente, $fecha)
        : $comprasController->listarPorCliente($idCliente);

    $datos = [];
    foreach ($compras as $compra) {
        $local = $localController->buscar($compra->getIdLocal());
        $datos[] = [
            'idRegistroCompra' => $compra->getIdRegistroCompra(),
            'idCliente' => $compra->getIdCliente(),
            'idLocal' => $compra->getIdLocal(),
            'nombreLocal' => $local?->getNombreLocal() ?? '(local no encontrado)',
            'fechaCompra' => $compra->getFechaCompra()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode(['exito' => true, 'compras' => $datos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'compras' => []]);
}
