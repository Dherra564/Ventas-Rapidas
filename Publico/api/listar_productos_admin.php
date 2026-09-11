<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $nombre = isset($_GET['nombre']) && $_GET['nombre'] !== '' ? $_GET['nombre'] : null;
    $soloActivos = ($_GET['soloActivos'] ?? '1') === '1';

    $controladorProducto = new ProductoController();
    $controladorLocal = new LocalController();

    $productos = $controladorProducto->buscarConFiltros($nombre, null, null, null, null, $soloActivos ? true : null);

    $cacheLocales = [];
    $datos = [];

    foreach ($productos as $producto) {
        $idLocal = $producto->getIdLocal();

        if (!isset($cacheLocales[$idLocal])) {
            $cacheLocales[$idLocal] = $controladorLocal->buscar($idLocal);
        }
        $local = $cacheLocales[$idLocal];

        $tipo = $controladorProducto->buscarTipoProducto($producto->getIdTipoProducto());

        $datos[] = [
            'idProducto' => $producto->getIdProducto(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'precioOriginal' => $producto->getPrecioOriginal(),
            'porcentajeDescuento' => $producto->getPorcentajeDescuento(),
            'cantidadDisponible' => $producto->getCantidadDisponible(),
            'imagen' => $producto->getImagen(),
            'activo' => $producto->isActivo(),
            'nombreLocal' => $local?->getNombreLocal() ?? 'Local eliminado',
            'categoria' => $tipo?->getNombre() ?? 'Otros'
        ];
    }

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}