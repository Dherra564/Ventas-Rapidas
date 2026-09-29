<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/LectorUbicaciones.php';

$idLocal = (int) ($_GET['id'] ?? 0);

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

    $provincia = LectorUbicaciones::provinciaPorId($ubicacion->getIdProvincia());
    $canton = LectorUbicaciones::cantonPorId($ubicacion->getIdCanton());
    $distrito = LectorUbicaciones::distritoPorId($ubicacion->getIdDistrito());

    echo json_encode([
        'exito' => true,
        'local' => [
            'idLocal' => $local->getIdLocal(),
            'idTipoLocal' => $local->getIdTipoLocal(),
            'tipoLocal' => $tipo?->getNombre(),
            'nombreLocal' => $local->getNombreLocal(),
            'descripcion' => $local->getDescripcion(),
            'telefono' => $local->getTelefono(),
            'logo' => $local->getLogo()
        ],
        'ubicacion' => [
            'provincia' => $provincia['nombre'] ?? null,
            'canton' => $canton['nombre'] ?? null,
            'distrito' => $distrito['nombre'] ?? null,
            'direccionExacta' => $ubicacion->getDireccionExacta(),
            'referencia' => $ubicacion->getReferencia(),
            'latitud' => $ubicacion->getLatitud(),
            'longitud' => $ubicacion->getLongitud()
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}