<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Local.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class EditarLocalHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new LocalController();

        $idLocal = (int) ($_POST['idLocal'] ?? 0);
        $localActual = $controlador->buscar($idLocal);

        if ($localActual === null) {
            return ['exito' => false, 'mensaje' => 'Local no encontrado'];
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
            $_POST['telefono'] ?? '',
            $_POST['correo'] ?? '',
            $_POST['descripcion'] ?? null,
            $logoFinal,
            true,
            $idLocal
        );

        $actualizado = $controlador->editar($local);

        return [
            'exito' => $actualizado,
            'mensaje' => $actualizado ? 'Local actualizado correctamente' : 'No se pudo actualizar el local'
        ];
    }
}

try {
    $handler = new EditarLocalHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);