<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);

$respuesta = ['exito' => false, 'mensaje' => ''];

try {
    $controlador = new LocalController();

    $creado = $controlador->registrar(
        (int)($datos['idProveedor'] ?? 0),
        $datos['nombreLocal'] ?? '',
        $datos['descripcion'] ?? null,
        $datos['telefono'] ?? '',
        $datos['correo'] ?? '',
        $datos['imagen'] ?? null,
        $datos['provincia'] ?? '',
        $datos['canton'] ?? '',
        $datos['distrito'] ?? '',
        $datos['direccionExacta'] ?? '',
        $datos['referencia'] ?? null
    );

    if ($creado) {
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = 'Local registrado correctamente';
    } else {
        $respuesta['mensaje'] = 'No se pudo registrar el local';
    }

} catch (InvalidArgumentException $e) {
    $respuesta['mensaje'] = $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($respuesta);