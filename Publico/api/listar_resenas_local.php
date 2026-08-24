<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ResenaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';

try {
    $idLocal = (int) ($_GET['idLocal'] ?? 0);
    if ($idLocal <= 0) {
        throw new InvalidArgumentException('Selecciona un local válido');
    }

    $resenaController = new ResenaController();
    $clienteController = new ClienteController();
    $resenas = $resenaController->listarPorLocal($idLocal);

    $datos = [];
    foreach ($resenas as $resena) {
        $cliente = $clienteController->buscar($resena->getIdCliente());
        $datos[] = [
            'idResena' => $resena->getIdResena(),
            'idCliente' => $resena->getIdCliente(),
            'nombreCliente' => $cliente?->getNombreCompleto() ?? '(cliente no encontrado)',
            'idLocal' => $resena->getIdLocal(),
            'comentario' => $resena->getComentario(),
            'puntuacion' => $resena->getPuntuacion(),
            'fechaResena' => $resena->getFechaResena()?->format('Y-m-d H:i:s')
        ];
    }

    echo json_encode([
        'exito' => true,
        'resenas' => $datos,
        'promedio' => $resenaController->promedioPorLocal($idLocal),
        'total' => $resenaController->totalResenasPorLocal($idLocal)
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'resenas' => [], 'promedio' => null, 'total' => 0]);
}
