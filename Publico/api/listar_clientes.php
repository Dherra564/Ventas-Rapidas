<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $termino = trim($_GET['termino'] ?? '');
    $estado = trim($_GET['estado'] ?? 'activos');

    $activo = match ($estado) {
        'todos' => null,
        'inactivos' => false,
        default => true,
    };

    $controlador = new ClienteController();
    $clientes = $controlador->buscarConFiltros($termino !== '' ? $termino : null, $activo);

    $datos = array_map(fn($c) => [
        'idCliente' => $c->getIdCliente(),
        'nombreCompleto' => $c->getNombreCompleto(),
        'numeroIdentificacion' => $c->getNumeroIdentificacion(),
        'correo' => $c->getCorreo(),
        'fotoPerfil' => $c->getFotoPerfil(),
        'activo' => $c->isActivo()
    ], $clientes);

    echo json_encode(['exito' => true, 'clientes' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}