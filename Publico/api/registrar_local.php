<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$datos = json_decode(file_get_contents('php://input'), true);
$respuesta = ['exito' => false, 'mensaje' => ''];

try {
    $controlador = new LocalController();

    $nombreLocal = $datos['nombreLocal'] ?? '';

    if ($controlador->existeNombreLocal($nombreLocal)) {
        echo json_encode(['exito' => false, 'mensaje' => 'Ya existe un local con ese nombre']);
        exit;
    }

    $idLocal = $controlador->registrar(
        (int)($datos['idComerciante'] ?? 0),
        $datos['nombreTipoLocal'] ?? '',
        $nombreLocal,
        $datos['telefono'] ?? '',
        $datos['correo'] ?? '',
        $datos['descripcion'] ?? null,
        $datos['productosAOfrecer'] ?? null,
        null,
        (int)($datos['idProvincia'] ?? 0),
        (int)($datos['idCanton'] ?? 0),
        (int)($datos['idDistrito'] ?? 0),
        $datos['direccionExacta'] ?? '',
        $datos['referencia'] ?? null
    );

    if ($idLocal !== false) {
        $respuesta['exito'] = true;
        $respuesta['mensaje'] = 'Local registrado correctamente';
        $respuesta['idLocal'] = $idLocal;
    } else {
        $respuesta['mensaje'] = 'No se pudo registrar el local';
    }

} catch (InvalidArgumentException $e) {
    $respuesta['mensaje'] = $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($respuesta);