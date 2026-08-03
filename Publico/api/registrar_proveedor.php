<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Aplicacion/Controladoras/ProveedorController.php';

$datos = json_decode(file_get_contents('php://input'), true);

$respuesta = ['exito' => false, 'mensaje' => ''];

try {
    $controlador = new ProveedorController();

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

    $creado = $controlador->registrar(
        $datos['nombre'] ?? '',
        $datos['apellido'] ?? '',
        $cedula,
        $correo,
        $datos['password'] ?? ''
    );

    if ($creado) {
        $proveedor = $controlador->buscarPorCedula($cedula);

        $respuesta['exito'] = true;
        $respuesta['mensaje'] = 'Proveedor registrado correctamente';
        $respuesta['idProveedor'] = $proveedor?->getIdProveedor();
        $respuesta['nombre'] = $proveedor?->getNombre();
        $respuesta['apellido'] = $proveedor?->getApellido();
    } else {
        $respuesta['mensaje'] = 'No se pudo registrar el proveedor';
    }

} catch (InvalidArgumentException $e) {
    $respuesta['mensaje'] = $e->getMessage();
} catch (Exception $e) {
    $respuesta['mensaje'] = 'Error del servidor: ' . $e->getMessage();
}

echo json_encode($respuesta);