<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/RegistroCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);

    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Selecciona un local válido');
    }

    $controlador = new RegistroCompraController();
    $id = $controlador->registrar($usuario['id'], $idLocal);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Compra registrada correctamente' : 'No se pudo registrar la compra',
        'idRegistroCompra' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
