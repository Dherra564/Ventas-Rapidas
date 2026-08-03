<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

$idLocal = (int)($_GET['id'] ?? 0);

try {
    $controlador = new LocalController();
    $resultado = $controlador->buscarConUbicacion($idLocal);

    if ($resultado === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'Local no encontrado']);
        exit;
    }

    $local = $resultado['local'];
    $ubicacion = $resultado['ubicacion'];

    echo json_encode([
        'exito' => true,
        'local' => [
            'idLocal' => $local->getIdLocal(),
            'idProveedor' => $local->getIdProveedor(),
            'nombreLocal' => $local->getNombreLocal(),
            'descripcion' => $local->getDescripcion(),
            'telefono' => $local->getTelefono(),
            'correo' => $local->getCorreo(),
            'imagen' => $local->getImagen()
        ],
        'ubicacion' => [
            'idUbicacion' => $ubicacion->getIdUbicacion(),
            'provincia' => $ubicacion->getProvincia(),
            'canton' => $ubicacion->getCanton(),
            'distrito' => $ubicacion->getDistrito(),
            'direccionExacta' => $ubicacion->getDireccionExacta(),
            'referencia' => $ubicacion->getReferencia()
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}