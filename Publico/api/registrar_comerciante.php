<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

$datos = json_decode(file_get_contents('php://input'), true);
$respuesta = ['exito' => false, 'mensaje' => ''];

try {
    $controlador = new ComercianteController();

    $cedula = $datos['cedula'] ?? '';
    $correo = $datos['correo'] ?? '';

    if ($controlador->existeCedula($cedula)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Esa cédula ya está registrada']);
        exit;
    }

    if ($controlador->existeCorreo($correo)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Ese correo ya está registrado']);
        exit;
    }

    $idComerciante = $controlador->registrar(
        $datos['nombre'] ?? '',
        $datos['alias'] ?? '',
        $cedula,
        $correo,
        $datos['password'] ?? ''
    );

    if ($idComerciante !== false) {
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = 'Comerciante registrado correctamente';
        $respuesta['idComerciante'] = $idComerciante;
    } else {
        $respuesta['mensaje'] = 'No se pudo registrar el comerciante';
    }

} catch (InvalidArgumentException $e) {
    $respuesta['mensaje'] = $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($respuesta);