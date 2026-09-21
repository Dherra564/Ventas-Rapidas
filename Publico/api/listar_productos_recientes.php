<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';

try {
    $limite = isset($_GET['limite']) ? (int) $_GET['limite'] : 8;

    $controlador = new ProductoController();
    $resultados = $controlador->listarRecientes($limite);

    $datos = array_map(function ($fila) {
        $p = $fila['producto'];
        return [
            'idProducto' => $p->getIdProducto(),
            'idLocal' => $p->getIdLocal(),
            'nombreLocal' => $fila['nombreLocal'],
            'logoLocal' => $fila['logoLocal'],
            'nombre' => $p->getNombre(),
            'descripcion' => $p->getDescripcion(),
            'precioOriginal' => $p->getPrecioOriginal(),
            'porcentajeDescuento' => $p->getPorcentajeDescuento(),
            'precioFinal' => $p->getPrecioFinal(),
            'imagen' => $p->getImagen(),
            'agotado' => $p->isAgotado()
        ];
    }, $resultados);

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'productos' => []]);
}