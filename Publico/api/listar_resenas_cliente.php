<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ResenaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';

try {
    $idCliente = (int) ($_GET['idCliente'] ?? 0);
    if ($idCliente <= 0) {
        throw new InvalidArgumentException('Selecciona un cliente válido');
    }

    $resenaController = new ResenaController();
    $localController = new LocalController();
    $resenas = $resenaController->listarPorCliente($idCliente);

    $datos = [];
    foreach ($resenas as $resena) {
        $local = $localController->buscar($resena->getIdLocal());
        $datos[] = [
            'idResena' => $resena->getIdResena(),
            'idCliente' => $resena->getIdCliente(),
            'idLocal' => $resena->getIdLocal(),
            'nombreLocal' => $local?->getNombreLocal() ?? '(local no encontrado)',
            'comentario' => $resena->getComentario(),
            'puntuacion' => $resena->getPuntuacion(),
            'fechaResena' => $resena->getFechaResena()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode(['exito' => true, 'resenas' => $datos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'resenas' => []]);
}
