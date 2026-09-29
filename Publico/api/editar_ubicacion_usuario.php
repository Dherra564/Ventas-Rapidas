<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/UbicacionController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

function resolverClienteUbicacion(array $usuario): Cliente
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

    throw new InvalidArgumentException('No tienes permiso para editar esta ubicación');
}

try {
    $cliente = resolverClienteUbicacion($usuario);

    $idProvincia = (int) ($datos['idProvincia'] ?? 0);
    $idCanton = (int) ($datos['idCanton'] ?? 0);
    $idDistrito = (int) ($datos['idDistrito'] ?? 0);
    $direccionExacta = trim($datos['direccionExacta'] ?? '');
    $referencia = trim($datos['referencia'] ?? '');
    $latitud = isset($datos['latitud']) && is_numeric($datos['latitud']) ? (float) $datos['latitud'] : null;
    $longitud = isset($datos['longitud']) && is_numeric($datos['longitud']) ? (float) $datos['longitud'] : null;

    $controlador = new UbicacionController();
    $exito = $controlador->actualizarUbicacionCliente(
        $cliente->getIdCliente(),
        $idProvincia,
        $idCanton,
        $idDistrito,
        $direccionExacta,
        $referencia !== '' ? $referencia : null,
        $latitud,
        $longitud
    );

    echo json_encode([
        'exito' => $exito,
        'mensaje' => $exito ? 'Ubicación actualizada correctamente' : 'No se pudo actualizar la ubicación'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}