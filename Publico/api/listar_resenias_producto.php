<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $idProducto = (int) ($_GET['idProducto'] ?? 0);
    if ($idProducto <= 0) {
        throw new InvalidArgumentException('Selecciona un producto válido');
    }

    $controlador = new ReseniaProductoController();
    $clienteControlador = new ClienteController();
    $resenias = $controlador->listarPorProducto($idProducto);

    $datos = [];
    foreach ($resenias as $resenia) {
        $cliente = $clienteControlador->buscar($resenia->getIdCliente());
        $datos[] = [
            'idReseniaProducto' => $resenia->getIdReseniaProducto(),
            'idCliente' => $resenia->getIdCliente(),
            'nombreCliente' => $cliente?->getNombreCompleto() ?? '(cliente no encontrado)',
            'idProducto' => $resenia->getIdProducto(),
            'comentario' => $resenia->getComentario(),
            'puntuacion' => $resenia->getPuntuacion(),
            'fecha' => $resenia->getFecha()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode([
        'exito' => true,
        'resenias' => $datos,
        'promedio' => $controlador->promedioPorProducto($idProducto),
        'total' => $controlador->totalPorProducto($idProducto)
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'resenias' => [], 'promedio' => null, 'total' => 0]);
}