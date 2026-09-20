<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuarioSesion = Sesion::requerirSesion();

class RegistrarLocalHandler
{
    use ManejadorImagenes;

    public function manejar(array $usuarioSesion): array
    {
        $idComerciante = $this->resolverIdComerciante($usuarioSesion);

        $controlador = new LocalController();

        $nombreLocal = $_POST['nombreLocal'] ?? '';

        if ($controlador->existeNombreLocal($nombreLocal)) {
            return ['exito' => false, 'mensaje' => 'Ya existe un local con ese nombre'];
        }

        $nombreLogo = $this->subirImagenPerfil($_FILES['logo'] ?? null, 'local');

        $idLocal = $controlador->registrar(
            $idComerciante,
            $_POST['nombreTipoLocal'] ?? '',
            $nombreLocal,
            preg_replace('/\D/', '', $_POST['telefono'] ?? ''),
            $_POST['descripcion'] ?? null,
            $nombreLogo !== false ? $nombreLogo : null,
            (int) ($_POST['idProvincia'] ?? 0),
            (int) ($_POST['idCanton'] ?? 0),
            (int) ($_POST['idDistrito'] ?? 0),
            $_POST['direccionExacta'] ?? '',
            $_POST['referencia'] ?? null,
            isset($_POST['latitud']) && $_POST['latitud'] !== '' ? (float) $_POST['latitud'] : null,
            isset($_POST['longitud']) && $_POST['longitud'] !== '' ? (float) $_POST['longitud'] : null
        );

        if ($idLocal !== false) {
            return [
                'exito' => true,
                'mensaje' => 'Local registrado correctamente',
                'idLocal' => $idLocal,
                'usuario' => Sesion::usuarioActual()
            ];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar el local'];
    }

    // Si ya es Comerciante, usa su ID directo. Si es Cliente, le crea el perfil de
    // Comerciante en este mismo momento (usando el alias del formulario) y "sube"
    // su sesión activa a Comerciante, sin que tenga que volver a iniciar sesión.
    private function resolverIdComerciante(array $usuarioSesion): int
    {
        if ($usuarioSesion['tipo'] === Sesion::TIPO_COMERCIANTE) {
            return $usuarioSesion['id'];
        }

        if ($usuarioSesion['tipo'] !== Sesion::TIPO_CLIENTE) {
            throw new InvalidArgumentException('No tienes permiso para registrar un local');
        }

        $clienteControlador = new ClienteController();
        $cliente = $clienteControlador->buscar($usuarioSesion['id']);

        if ($cliente === null) {
            throw new InvalidArgumentException('No se pudo identificar tu cuenta');
        }

        $comercianteControlador = new ComercianteController();
        $comercianteExistente = $comercianteControlador->buscarPorIdUsuario($cliente->getIdUsuario());

        if ($comercianteExistente !== null) {
            Sesion::iniciarSesionUsuario($comercianteExistente->getIdComerciante(), Sesion::TIPO_COMERCIANTE, $comercianteExistente->getNombreCompleto());
            return $comercianteExistente->getIdComerciante();
        }

        $alias = trim($_POST['alias'] ?? '');
        if ($alias === '') {
            throw new InvalidArgumentException('Escribe el nombre con el que quieres que te conozcan como vendedor');
        }

        $idComerciante = $comercianteControlador->registrar($cliente->getIdUsuario(), $alias);
        if ($idComerciante === false) {
            throw new Exception('No se pudo crear tu perfil de vendedor');
        }

        Sesion::iniciarSesionUsuario($idComerciante, Sesion::TIPO_COMERCIANTE, $cliente->getNombreCompleto());
        return $idComerciante;
    }
}

try {
    $handler = new RegistrarLocalHandler();
    $respuesta = $handler->manejar($usuarioSesion);
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);