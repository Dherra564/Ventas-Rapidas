<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Local.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Ubicacion.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

class EditarLocalHandler
{
    use ManejadorImagenes;

    public function manejar(int $idComerciante): array
    {
        $controlador = new LocalController();

        $idLocal = (int) ($_POST['idLocal'] ?? 0);
        $localActual = $controlador->buscar($idLocal);

        if ($localActual === null) {
            return ['exito' => false, 'mensaje' => 'Local no encontrado'];
        }

        if (!$controlador->perteneceAComerciante($idLocal, $idComerciante)) {
            http_response_code(403);
            return ['exito' => false, 'mensaje' => 'Ese local no pertenece a tu cuenta'];
        }

        $idTipoLocal = $controlador->resolverTipoLocal($_POST['nombreTipoLocal'] ?? '');

        $nombreLogoNuevo = $this->subirImagenPerfil($_FILES['logo'] ?? null, 'local');

        if ($nombreLogoNuevo !== false) {
            $this->eliminarImagen($localActual->getLogo());
            $logoFinal = $nombreLogoNuevo;
        } else {
            $logoFinal = $localActual->getLogo();
        }

        $local = new Local(
            $idTipoLocal,
            $_POST['nombreLocal'] ?? '',
            preg_replace('/\D/', '', $_POST['telefono'] ?? ''),
            $_POST['descripcion'] ?? null,
            $logoFinal,
            true,
            $idLocal,
            null,
            preg_replace('/\D/', '', $_POST['numeroSinpe'] ?? '')
        );

        $ubicacion = null;
        $idProvincia = (int) ($_POST['idProvincia'] ?? 0);

        if ($idProvincia > 0) {
            $datosActuales = $controlador->buscarConUbicacion($idLocal);
            $ubicacionActual = $datosActuales['ubicacion'] ?? null;

            $latitud = isset($_POST['latitud']) && $_POST['latitud'] !== '' ? (float) $_POST['latitud'] : $ubicacionActual?->getLatitud();
            $longitud = isset($_POST['longitud']) && $_POST['longitud'] !== '' ? (float) $_POST['longitud'] : $ubicacionActual?->getLongitud();

            $ubicacion = new Ubicacion(
                $idLocal,
                $idProvincia,
                (int) ($_POST['idCanton'] ?? 0),
                (int) ($_POST['idDistrito'] ?? 0),
                $_POST['direccionExacta'] ?? '',
                $_POST['referencia'] ?? null,
                null,
                true,
                $ubicacionActual?->getIdUbicacion() ?? 0,
                $latitud,
                $longitud
            );
        }

        $actualizado = $controlador->editar($local, $idComerciante, $ubicacion);

        return [
            'exito' => $actualizado,
            'mensaje' => $actualizado ? 'Local actualizado correctamente' : 'No se pudo actualizar el local'
        ];
    }
}

try {
    $handler = new EditarLocalHandler();
    $respuesta = $handler->manejar($usuario['id']);
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);