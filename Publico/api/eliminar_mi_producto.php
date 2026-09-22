<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

$datos = json_decode(file_get_contents('php://input'), true);
$idProducto = (int) ($datos['idProducto'] ?? 0);

try {
    $controlador = new ProductoController();
    $producto = $controlador->buscar($idProducto);

    if ($producto === null) {
        throw new InvalidArgumentException('Producto no encontrado');
    }

    $localControlador = new LocalController();
    if (!$localControlador->perteneceAComerciante($producto->getIdLocal(), $usuario['id'])) {
        http_response_code(403);
        throw new InvalidArgumentException('Ese producto no pertenece a tu cuenta');
    }

    $exito = $controlador->eliminar($idProducto);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Producto eliminado correctamente' : 'No se pudo eliminar el producto'
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}