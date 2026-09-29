<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/CarritoCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $idProducto = (int) ($datos['idProducto'] ?? 0);
    $cantidad = filter_var($datos['cantidad'] ?? 1, FILTER_VALIDATE_INT);

    if ($cantidad === false) {
        throw new InvalidArgumentException('La cantidad debe ser un número entero mayor a 0');
    }

    $controlador = new CarritoCompraController();
    $resultado = $controlador->agregarProducto($usuario, $idLocal, $idProducto, $cantidad);

    echo json_encode([
        'exito' => true,
        'mensaje' => 'Producto agregado al carrito',
        'localNombre' => $resultado['localNombre'],
        'productoNombre' => $resultado['productoNombre'],
        'cantidadEnCarrito' => $resultado['cantidadEnCarrito'],
        'totalProductos' => $resultado['totalProductos']
    ]);
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo agregar al carrito: ' . $e->getMessage()]);
}