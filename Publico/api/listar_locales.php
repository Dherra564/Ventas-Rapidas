<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $controlador = new LocalController();
    $locales = $controlador->listar();

    $datos = [];
    foreach ($locales as $local) {
        $datos[] = [
            'idLocal'     => $local->getIdLocal(),
            'nombreLocal' => $local->getNombreLocal(),
            'descripcion' => $local->getDescripcion(),
            'telefono'    => $local->getTelefono(),
            'correo'      => $local->getCorreo(),
            'imagen'      => $local->getImagen(),
        ];
    }

    echo json_encode(['exito' => true, 'locales' => $datos]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}