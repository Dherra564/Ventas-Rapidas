<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

try {
    $soloActivos = ($_GET['soloActivos'] ?? '1') === '1';

    $controlador = new ComercianteController();
    $comerciantes = $controlador->buscarConFiltros(null, null, $soloActivos ? true : null);

    $datos = array_map(fn($c) => [
        'idComerciante' => $c->getIdComerciante(),
        'nombre' => $c->getNombreCompleto(),
        'alias' => $c->getAlias(),
        'numeroIdentificacion' => $c->getNumeroIdentificacion(),
        'correo' => $c->getCorreo(),
        'fotoPerfil' => $c->getFotoPerfil(),
        'activo' => $c->isActivo()

    ], $comerciantes);

    echo json_encode(['exito' => true, 'comerciantes' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}