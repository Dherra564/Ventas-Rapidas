<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $idLocal = (int) ($_GET['idLocal'] ?? 0);
    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Selecciona un local válido');
    }

    $reseniaController = new ReseniaController();
    $clienteController = new ClienteController();
    $resenias = $reseniaController->listarPorLocal($idLocal);

    $datos = [];
    foreach ($resenias as $resenia) {
        $cliente = $clienteController->buscar($resenia->getIdCliente());
        $datos[] = [
            'idResenia' => $resenia->getIdResenia(),
            'idCliente' => $resenia->getIdCliente(),
            'nombreCliente' => $cliente?->getNombreCompleto() ?? '(cliente no encontrado)',
            'idLocal' => $resenia->getIdLocal(),
            'comentario' => $resenia->getComentario(),
            'puntuacion' => $resenia->getPuntuacion(),
            'fechaResenia' => $resenia->getFechaResenia()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode([
        'exito' => true,
        'resenias' => $datos,
        'promedio' => $reseniaController->promedioPorLocal($idLocal),
        'total' => $reseniaController->totalReseniasPorLocal($idLocal)
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'resenias' => [], 'promedio' => null, 'total' => 0]);
}