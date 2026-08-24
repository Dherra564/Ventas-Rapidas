<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    $idUsuario = (int) ($datos['idUsuario'] ?? 0);
    $tipoUsuario = trim($datos['tipoUsuario'] ?? '');
    $passwordActual = (string) ($datos['passwordActual'] ?? '');
    $passwordNueva = (string) ($datos['passwordNueva'] ?? '');

    if ($idUsuario <= 0) {
        throw new InvalidArgumentException('Selecciona un usuario válido');
    }

    if ($tipoUsuario === 'Cliente') {
        $controlador = new ClienteController();
        $exito = $controlador->cambiarPassword($idUsuario, $passwordActual, $passwordNueva);
    } elseif ($tipoUsuario === 'Comerciante') {
        $controlador = new ComercianteController();
        $exito = $controlador->cambiarPassword($idUsuario, $passwordActual, $passwordNueva);
    } else {
        throw new InvalidArgumentException('Tipo de usuario inválido');
    }

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Contraseña cambiada correctamente' : 'No se pudo cambiar la contraseña'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
