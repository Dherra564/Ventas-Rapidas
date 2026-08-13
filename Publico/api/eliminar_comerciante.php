<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$datos = json_decode(file_get_contents('php://input'), true);
$idComerciante = (int) ($datos['idComerciante'] ?? 0);

try {
    $controlador = new ComercianteController();
    $exito = $controlador->eliminar($idComerciante);

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Comerciante desactivado correctamente' : 'No se pudo desactivar el comerciante'
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}