<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/RegistroCompraController.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idCliente = (int) ($datos['idCliente'] ?? 0);
    $idLocal = (int) ($datos['idLocal'] ?? 0);

    if ($idCliente <= 0 || $idLocal <= 0) {
        throw new InvalidArgumentException('Selecciona un cliente y un local válidos');
    }

    $controlador = new RegistroCompraController();
    $id = $controlador->registrar($idCliente, $idLocal);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Compra registrada correctamente' : 'No se pudo registrar la compra',
        'idRegistroCompra' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
