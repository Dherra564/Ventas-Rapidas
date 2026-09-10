<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $latitud = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
    $longitud = isset($_GET['lng']) ? (float) $_GET['lng'] : null;
    $radioKm = isset($_GET['radio']) ? (float) $_GET['radio'] : 15;

    if ($latitud === null || $longitud === null) {
        http_response_code(400);
        echo json_encode(['exito' => false, 'mensaje' => 'Faltan las coordenadas de ubicación (lat/lng)']);
        exit;
    }

    $controlador = new LocalController();
    $resultados = $controlador->buscarCercanos($latitud, $longitud, $radioKm);

    echo json_encode(['exito' => true, 'locales' => $resultados]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}