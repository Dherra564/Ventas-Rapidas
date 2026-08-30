<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteLocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_CLIENTE);

$datos = json_decode(file_get_contents('php://input'), true);

try {
    $idClienteLocal = (int) ($datos['idClienteLocal'] ?? 0);
    $controlador = new ClienteLocalController();

    if (!$controlador->perteneceACliente($idClienteLocal, $usuario['id'])) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'Esa relación no pertenece a tu cuenta']);
        exit;
    }

    $exito = $controlador->dejarDeSeguir($idClienteLocal);

    echo json_encode(['exito' => $exito]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}