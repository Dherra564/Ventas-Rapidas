<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

$idCliente = (int) ($_GET['id'] ?? 0);

try {
    $controlador = new ClienteController();
    $resultado = $controlador->buscarConUbicacion($idCliente);

    if ($resultado === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'Cliente no encontrado']);
        exit;
    }

    $cliente = $resultado['cliente'];
    $ubicacion = $resultado['ubicacion'];

    echo json_encode([
        'exito' => true,
        'cliente' => [
            'idCliente' => $cliente->getIdCliente(),
            'nombreCompleto' => $cliente->getNombreCompleto(),
            'numeroIdentificacion' => $cliente->getNumeroIdentificacion(),
            'correo' => $cliente->getCorreo(),
            'fotoPerfil' => $cliente->getFotoPerfil(),
            'activo' => $cliente->isActivo()
        ],
        'ubicacion' => [
            'direccionExacta' => $ubicacion->getDireccionExacta(),
            'referencia' => $ubicacion->getReferencia()
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}