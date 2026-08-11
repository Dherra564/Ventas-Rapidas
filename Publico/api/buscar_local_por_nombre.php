<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$nombre = trim($_GET['nombre'] ?? '');

try {
    $controlador = new LocalController();
    $locales = $controlador->buscarConFiltros($nombre);

    $coincidenciaExacta = null;
    foreach ($locales as $local) {
        if (strcasecmp($local->getNombreLocal(), $nombre) === 0) {
            $coincidenciaExacta = $local;
            break;
        }
    }

    if ($coincidenciaExacta === null) {
        echo json_encode(['encontrado' => false]);
        exit;
    }

    echo json_encode([
        'encontrado' => true,
        'idLocal' => $coincidenciaExacta->getIdLocal()
    ]);

} catch (Exception $e) {
    echo json_encode(['encontrado' => false]);
}