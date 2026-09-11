<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $termino = trim($_GET['termino'] ?? '');

    if ($termino === '') {
        echo json_encode(['exito' => true, 'productos' => []]);
        exit;
    }

    $controladorProducto = new ProductoController();
    $controladorLocal = new LocalController();

    $productos = $controladorProducto->buscarConFiltros($termino);

    $datos = array_map(function ($p) use ($controladorLocal) {
        $local = $controladorLocal->buscar($p->getIdLocal());
        return [
            'idProducto' => $p->getIdProducto(),
            'nombre' => $p->getNombre(),
            'nombreLocal' => $local?->getNombreLocal() ?? ''
        ];
    }, $productos);

    echo json_encode(['exito' => true, 'productos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}