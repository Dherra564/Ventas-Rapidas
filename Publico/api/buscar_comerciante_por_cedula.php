<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$cedula = trim($_GET['cedula'] ?? '');

try {
    $controlador = new ComercianteController();
    $comerciante = $controlador->buscarPorCedula($cedula);

    if ($comerciante === null) {
        echo json_encode(['encontrado' => false]);
        exit;
    }

    echo json_encode([
        'encontrado' => true,
        'idComerciante' => $comerciante->getIdComerciante(),
        'nombre' => $comerciante->getNombreCompleto(),
        'alias' => $comerciante->getAlias()
    ]);

} catch (Exception $e) {
    echo json_encode(['encontrado' => false]);
}