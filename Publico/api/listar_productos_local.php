<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Repositorios/ProductoRepository.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::usuarioActual();

$idLocal = (int) ($_GET['idLocal'] ?? 0);

try {

    $controlador = new ProductoController();
    $productos = $controlador->listarPorLocal($idLocal);

    $repoProducto = new ProductoRepository();
    $productosPropios = $repoProducto->obtenerPorLocal($idLocal);
    $idsPropios = array_map(fn($p) => $p->getIdProducto(), $productosPropios);

    $datos = array_map(fn($p) => [
        'idProducto' => $p->getIdProducto(),
        'nombre' => $p->getNombre(),
        'descripcion' => $p->getDescripcion(),
        'precioOriginal' => $p->getPrecioOriginal(),
        'porcentajeDescuento' => $p->getPorcentajeDescuento(),
        'precioFinal' => $p->getPrecioFinal(),
        'cantidadDisponible' => $p->getCantidadDisponible(),
        'agotado' => $p->isAgotado(),
        'imagen' => $p->getImagen(),
        'compartido' => !in_array($p->getIdProducto(), $idsPropios, true)
    ], $productos);

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
