<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $soloActivos = ($_GET['soloActivos'] ?? '1') === '1';

    $controlador = new ClienteController();
    $clientes = $controlador->buscarConFiltros(null, $soloActivos ? true : null);
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