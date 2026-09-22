<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

try {
    $termino = trim($_GET['termino'] ?? '');
    $estado = trim($_GET['estado'] ?? 'activos');

    $activo = match ($estado) {
        'todos' => null,
        'inactivos' => false,
        default => true,
    };

    $controlador = new ComercianteController();
    $comerciantes = $controlador->buscarConFiltros($termino !== '' ? $termino : null, null, $activo);

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