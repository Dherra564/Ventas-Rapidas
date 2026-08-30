<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $idCliente = (int) ($_GET['idCliente'] ?? 0);
    if ($idCliente <= 0) {
        throw new InvalidArgumentException('Selecciona un cliente válido');
    }

    $reseniaController = new ReseniaController();
    $localController = new LocalController();
    $resenias = $reseniaController->listarPorCliente($idCliente);

    $datos = [];
    foreach ($resenias as $resenia) {
        $local = $localController->buscar($resenia->getIdLocal());
        $datos[] = [
            'idResenia' => $resenia->getIdResenia(),
            'idCliente' => $resenia->getIdCliente(),
            'idLocal' => $resenia->getIdLocal(),
            'nombreLocal' => $local?->getNombreLocal() ?? '(local no encontrado)',
            'comentario' => $resenia->getComentario(),
            'puntuacion' => $resenia->getPuntuacion(),
            'fechaResenia' => $resenia->getFechaResenia()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode(['exito' => true, 'resenias' => $datos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'resenias' => []]);
}