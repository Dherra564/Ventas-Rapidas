<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/RegistroCompraController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $limite = max(1, min(20, (int) ($_GET['limite'] ?? 10)));
    $comprasController = new RegistroCompraController();
    $localController = new LocalController();
    $ranking = $comprasController->localesMasComprados($limite);

    foreach ($ranking as &$fila) {
        $local = $localController->buscar((int) $fila['idLocal']);
        $fila['nombreLocal'] = $local?->getNombreLocal() ?? '(local no encontrado)';
        $fila['totalCompras'] = (int) $fila['totalCompras'];
    }
    unset($fila);

    echo json_encode(['exito' => true, 'locales' => $ranking]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'locales' => []]);
}
