<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoLocalController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $idProductoLocal = (int) ($datos['idProductoLocal'] ?? 0);

    $controlador = new ProductoLocalController();
    $relacion = $controlador->buscar($idProductoLocal);

    if ($relacion === null) {
        throw new InvalidArgumentException('Esa relación producto-local no existe');
    }

    $productoController = new ProductoController();
    $producto = $productoController->buscar((int) $relacion['tbproductoid']);

    if ($producto === null) {
        throw new InvalidArgumentException('Producto no encontrado');
    }

    $localController = new LocalController();
    if (!$localController->perteneceAComerciante($producto->getIdLocal(), $usuario['id'])) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Ese producto no pertenece a tu cuenta']);
        exit;
    }

    $exito = $controlador->quitar($idProductoLocal);

    echo json_encode(['exito' => $exito]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}