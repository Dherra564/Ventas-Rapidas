<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

try {
    $termino = trim($_GET['q'] ?? '');
    $latitud = isset($_GET['lat']) ? (float) $_GET['lat'] : null;
    $longitud = isset($_GET['lng']) ? (float) $_GET['lng'] : null;
    $radioKm = isset($_GET['radio']) ? (float) $_GET['radio'] : 5;

    if ($latitud === null || $longitud === null) {
        throw new InvalidArgumentException('Faltan las coordenadas de ubicación (lat/lng)');
    }

    (new LocalController())->sincronizarActividad();

    $controlador = new ProductoController();
    $resultados = $controlador->buscarLocalesCercanos($termino, $latitud, $longitud, $radioKm);

    echo json_encode(['exito' => true, 'resultados' => $resultados]);
} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}