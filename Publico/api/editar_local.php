<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Local.php';

$datos = json_decode(file_get_contents('php://input'), true);
$respuesta = ['exito' => false, 'mensaje' => ''];

try {
    $controlador = new LocalController();

    $idTipoLocal = $controlador->resolverTipoLocal($datos['nombreTipoLocal'] ?? '');

    $local = new Local(
        $idTipoLocal,
        $datos['nombreLocal'] ?? '',
        $datos['telefono'] ?? '',
        $datos['correo'] ?? '',
        $datos['descripcion'] ?? null,
        $datos['productosAOfrecer'] ?? null,
        null,
        true,
        (int)($datos['idLocal'] ?? 0)
    );

    $actualizado = $controlador->editar($local);

    $respuesta['exito'] = $actualizado;
    $respuesta['mensaje'] = $actualizado ? 'Local actualizado correctamente' : 'No se pudo actualizar el local';

} catch (InvalidArgumentException $e) {
    $respuesta['mensaje'] = $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($respuesta);