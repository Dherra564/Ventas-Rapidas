<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

try {
    $alias = trim($_POST['alias'] ?? '');

    if ($alias === '') {
        throw new InvalidArgumentException('El alias del negocio es obligatorio');
    }

    $controlador = new ComercianteController();
    $idComerciante = $controlador->registrar($usuario['id'], $alias);

    if ($idComerciante !== false) {
        echo json_encode([
            'exito' => true,
            'mensaje' => 'Ahora también eres comerciante',
            'idComerciante' => $idComerciante
        ]);
    } else {
        echo json_encode(['exito' => false, 'mensaje' => 'No se pudo registrar el perfil de comerciante']);
    }
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}