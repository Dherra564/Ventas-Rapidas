<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

function resolverClienteResenaProducto(array $usuario): Cliente
{
    if ($usuario['tipo'] === Sesion::TIPO_CLIENTE) {
        $cliente = (new ClienteController())->buscar($usuario['id']);
        if ($cliente === null) {
            throw new InvalidArgumentException('No se pudo identificar tu cuenta');
        }
        return $cliente;
    }

    if ($usuario['tipo'] === Sesion::TIPO_COMERCIANTE) {
        $comerciante = (new ComercianteController())->buscar($usuario['id']);
        if ($comerciante === null) {
            throw new InvalidArgumentException('No se pudo identificar tu cuenta');
        }

        $cliente = (new ClienteController())->buscarPorIdUsuario($comerciante->getIdUsuario());
        if ($cliente === null) {
            throw new InvalidArgumentException('No se pudo identificar tu perfil de cliente');
        }

        return $cliente;
    }

    throw new InvalidArgumentException('No tienes permiso para escribir reseñas');
}

try {
    $cliente = resolverClienteResenaProducto($usuario);

    $idProducto = (int) ($datos['idProducto'] ?? 0);
    $comentario = trim($datos['comentario'] ?? '');
    $puntuacion = (int) ($datos['puntuacion'] ?? 0);

    $producto = (new ProductoController())->buscar($idProducto);
    if ($producto === null) {
        throw new InvalidArgumentException('Ese producto no existe');
    }

    if ($usuario['tipo'] === Sesion::TIPO_COMERCIANTE) {
        $localControlador = new LocalController();
        if ($localControlador->perteneceAComerciante($producto->getIdLocal(), $usuario['id'])) {
            throw new InvalidArgumentException('No puedes escribir una reseña sobre tu propio producto');
        }
    }

    $pedidoControlador = new PedidoController();
    if (!$pedidoControlador->clienteComproProducto($cliente->getIdCliente(), $idProducto)) {
        throw new InvalidArgumentException('Solo puedes reseñar productos que hayas comprado y que el comerciante ya haya confirmado');
    }

    $controlador = new ReseniaProductoController();
    $id = $controlador->registrar($cliente->getIdCliente(), $idProducto, $comentario, $puntuacion);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Reseña publicada correctamente' : 'No se pudo publicar la reseña',
        'idReseniaProducto' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}