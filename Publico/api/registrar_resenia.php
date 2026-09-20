<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ReseniaController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

$datos = json_decode(file_get_contents('php://input'), true) ?? [];

// Sin importar si la sesión es de Cliente o de Comerciante, siempre hay un
// Cliente detrás (todo usuario es Cliente por defecto) — lo resolvemos aquí.
function resolverCliente(array $usuario): Cliente
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
    $cliente = resolverCliente($usuario);

    $idLocal = (int) ($datos['idLocal'] ?? 0);
    $comentario = trim($datos['comentario'] ?? '');
    $puntuacion = (int) ($datos['puntuacion'] ?? 0);

    if ($usuario['tipo'] === Sesion::TIPO_COMERCIANTE) {
        $localControlador = new LocalController();
        if ($localControlador->perteneceAComerciante($idLocal, $usuario['id'])) {
            throw new InvalidArgumentException('No puedes escribir una reseña sobre tu propio local');
        }
    }

    $controlador = new ReseniaController();
    $id = $controlador->registrar($cliente->getIdCliente(), $idLocal, $comentario, $puntuacion);

    echo json_encode([
        'exito' => $id !== false,
        'mensaje' => $id !== false ? 'Reseña publicada correctamente' : 'No se pudo publicar la reseña',
        'idResenia' => $id !== false ? $id : null
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}
