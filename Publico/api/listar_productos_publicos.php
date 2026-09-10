<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $controladorProducto = new ProductoController();
    $controladorLocal = new LocalController();

    // true = solo productos activos
    $productos = $controladorProducto->buscarConFiltros(null, null, null, null, null, true);

    $cacheLocales = [];
    $datos = [];

    foreach ($productos as $producto) {
        if ($producto->getCantidadDisponible() <= 0) {
            continue; // solo lo que está disponible ahora mismo
        }

        if ($producto->isVencido()) {
            continue; // ya venció, no se muestra en el catálogo público
        }

        $idLocal = $producto->getIdLocal();

        if (!isset($cacheLocales[$idLocal])) {
            $cacheLocales[$idLocal] = $controladorLocal->buscar($idLocal);
        }
        $local = $cacheLocales[$idLocal];

        if ($local === null || !$local->isActivo()) {
            continue;
        }

        $tipo = $controladorProducto->buscarTipoProducto($producto->getIdTipoProducto());

        $datos[] = [
            'idProducto' => $producto->getIdProducto(),
            'nombre' => $producto->getNombre(),
            'descripcion' => $producto->getDescripcion(),
            'precioOriginal' => $producto->getPrecioOriginal(),
            'porcentajeDescuento' => $producto->getPorcentajeDescuento(),
            'precioFinal' => $producto->getPrecioFinal(),
            'cantidadDisponible' => $producto->getCantidadDisponible(),
            'imagen' => $producto->getImagen(),
            'idLocal' => $idLocal,
            'nombreLocal' => $local->getNombreLocal(),
            'logoLocal' => $local->getLogo(),
            'categoria' => $tipo?->getNombre() ?? 'Otros',
            'fechaVencimiento' => $producto->getFechaVencimiento()?->format('Y-m-d\TH:i:s')
        ];
    }

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}