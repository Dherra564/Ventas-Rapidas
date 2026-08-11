<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';

$idProducto = (int) ($_GET['id'] ?? 0);

try {
    $controlador = new ProductoController();
    $producto = $controlador->buscar($idProducto);

    if ($producto === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'Producto no encontrado']);
        exit;
    }

    $tipo = $controlador->buscarTipoProducto($producto->getIdTipoProducto());

    echo json_encode([
        'exito' => true,
        'producto' => [
            'idProducto' => $producto->getIdProducto(),
            'idLocal' => $producto->getIdLocal(),
            'tipoProducto' => $tipo?->getNombre(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'precioOriginal' => $producto->getPrecioOriginal(),
            'porcentajeDescuento' => $producto->getPorcentajeDescuento(),
            'cantidadDisponible' => $producto->getCantidadDisponible(),
            'imagen' => $producto->getImagen()
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}