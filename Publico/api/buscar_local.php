<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProvinciaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/CantonController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/DistritoController.php';

$idLocal = (int)($_GET['id'] ?? 0);

try {
    $localControlador = new LocalController();
    $resultado = $localControlador->buscarConUbicacion($idLocal);

    if ($resultado === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'Local no encontrado']);
        exit;
    }

    $local = $resultado['local'];
    $ubicacion = $resultado['ubicacion'];

    $tipo = $localControlador->buscarTipoLocal($local->getIdTipoLocal());

    $provincia = (new ProvinciaController())->buscar($ubicacion->getIdProvincia());
    $canton = (new CantonController())->buscar($ubicacion->getIdCanton());
    $distrito = (new DistritoController())->buscar($ubicacion->getIdDistrito());

    echo json_encode([
        'exito' => true,
        'local' => [
            'idLocal' => $local->getIdLocal(),
            'idTipoLocal' => $local->getIdTipoLocal(),
            'tipoLocal' => $tipo?->getNombre(),
            'nombreLocal' => $local->getNombreLocal(),
            'descripcion' => $local->getDescripcion(),
            'productosAOfrecer' => $local->getProductosAOfrecer(),
            'telefono' => $local->getTelefono(),
            'correo' => $local->getCorreo()
        ],
        'ubicacion' => [
            'provincia' => $provincia?->getNombre(),
            'canton' => $canton?->getNombre(),
            'distrito' => $distrito?->getNombre(),
            'direccionExacta' => $ubicacion->getDireccionExacta(),
            'referencia' => $ubicacion->getReferencia()
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}