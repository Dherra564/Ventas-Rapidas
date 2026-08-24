<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/LocalController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class RegistrarLocalHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new LocalController();

        $nombreLocal = $_POST['nombreLocal'] ?? '';

        if ($controlador->existeNombreLocal($nombreLocal)) {
            return ['exito' => false, 'mensaje' => 'Ya existe un local con ese nombre'];
        }

        if ($controlador->existeCorreoLocal($_POST['correo'] ?? '')) {
            return ['exito' => false, 'mensaje' => 'Ya existe un local registrado con ese correo'];
        }

        $nombreLogo = $this->subirImagenPerfil($_FILES['logo'] ?? null, 'local');

        $idLocal = $controlador->registrar(
            (int) ($_POST['idComerciante'] ?? 0),
            $_POST['nombreTipoLocal'] ?? '',
            $nombreLocal,
            preg_replace('/\D/', '', $_POST['telefono'] ?? ''),
            $_POST['correo'] ?? '',
            $_POST['descripcion'] ?? null,
            $nombreLogo !== false ? $nombreLogo : null,
            (int) ($_POST['idProvincia'] ?? 0),
            (int) ($_POST['idCanton'] ?? 0),
            (int) ($_POST['idDistrito'] ?? 0),
            $_POST['direccionExacta'] ?? '',
            $_POST['referencia'] ?? null
        );

        if ($idLocal !== false) {
            return [
                'exito' => true,
                'mensaje' => 'Local registrado correctamente',
                'idLocal' => $idLocal
            ];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar el local'];
    }
}

try {
    $handler = new RegistrarLocalHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);