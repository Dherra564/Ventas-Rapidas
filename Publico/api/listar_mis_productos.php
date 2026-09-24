<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Repositorios/ProductoRepository.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

try {
    $localControlador = new LocalController();
    $locales = $localControlador->listarPorComerciante($usuario['id']);

    $repoProducto = new ProductoRepository();

    $datos = [];
    foreach ($locales as $local) {
        $productos = $repoProducto->obtenerPorLocal($local->getIdLocal());
        foreach ($productos as $p) {
            $datos[] = [
                'idProducto' => $p->getIdProducto(),
                'idLocal' => $local->getIdLocal(),
                'nombreLocal' => $local->getNombreLocal(),
                'nombre' => $p->getNombre(),
                'descripcion' => $p->getDescripcion(),
                'precioOriginal' => $p->getPrecioOriginal(),
                'porcentajeDescuento' => $p->getPorcentajeDescuento(),
                'precioFinal' => $p->getPrecioFinal(),
                'cantidadDisponible' => $p->getCantidadDisponible(),
                'agotado' => $p->isAgotado(),
                'imagen' => $p->getImagen()
            ];
        }
    }

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}