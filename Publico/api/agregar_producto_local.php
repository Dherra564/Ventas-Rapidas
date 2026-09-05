<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoLocalController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $idProducto = (int) ($datos['idProducto'] ?? 0);
    $idLocal = (int) ($datos['idLocal'] ?? 0);

    $productoController = new ProductoController();
    $producto = $productoController->buscar($idProducto);

    if ($producto === null) {
        throw new InvalidArgumentException('Producto no encontrado');
    }

    $localController = new LocalController();
    if (!$localController->perteneceAComerciante($producto->getIdLocal(), $usuario['id'])) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Ese producto no pertenece a tu cuenta']);
        exit;
    }

    $controlador = new ProductoLocalController();
    $id = $controlador->agregar($idProducto, $idLocal);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Local agregado al producto' : 'No se pudo agregar el local'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}