<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $controlador = new LocalController();

    $nombre = isset($_GET['nombre']) && $_GET['nombre'] !== '' ? $_GET['nombre'] : null;
    $idTipoLocal = isset($_GET['idTipoLocal']) && $_GET['idTipoLocal'] !== '' ? (int) $_GET['idTipoLocal'] : null;
    $idProvincia = isset($_GET['idProvincia']) && $_GET['idProvincia'] !== '' ? (int) $_GET['idProvincia'] : null;
    $idCanton = isset($_GET['idCanton']) && $_GET['idCanton'] !== '' ? (int) $_GET['idCanton'] : null;
    $idDistrito = isset($_GET['idDistrito']) && $_GET['idDistrito'] !== '' ? (int) $_GET['idDistrito'] : null;

    $locales = $controlador->buscarConFiltros($nombre, $idTipoLocal, $idProvincia, $idCanton, $idDistrito, true);

    $datos = [];
    foreach ($locales as $local) {
        $tipo = $controlador->buscarTipoLocal($local->getIdTipoLocal());

        $datos[] = [
            'idLocal' => $local->getIdLocal(),
            'nombreLocal' => $local->getNombreLocal(),
            'descripcion' => $local->getDescripcion(),
            'telefono' => $local->getTelefono(),
            'correo' => $local->getCorreo(),
            'tipoLocal' => $tipo?->getNombre(),
            'logo' => $local->getLogo()
        ];
    }

    echo json_encode(['exito' => true, 'locales' => $datos]);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}