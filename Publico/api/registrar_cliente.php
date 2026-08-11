<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class RegistrarClienteHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new ClienteController();

        $numeroIdentificacion = trim($_POST['numeroIdentificacion'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        if ($controlador->existeIdentificacion($numeroIdentificacion)) {
            return ['exito' => false, 'mensaje' => 'Ese número de identificación ya está registrado'];
        }

        if ($controlador->existeCorreo($correo)) {
            return ['exito' => false, 'mensaje' => 'Ese correo ya está registrado'];
        }

        $nombreImagen = $this->subirImagenPerfil($_FILES['fotoPerfil'] ?? null, 'cliente');

        $idCliente = $controlador->registrar(
            $_POST['nombreCompleto'] ?? '',
            $numeroIdentificacion,
            $correo,
            $_POST['password'] ?? '',
            $nombreImagen !== false ? $nombreImagen : '',
            (int) ($_POST['idProvincia'] ?? 0),
            (int) ($_POST['idCanton'] ?? 0),
            (int) ($_POST['idDistrito'] ?? 0),
            $_POST['direccionExacta'] ?? '',
            $_POST['referencia'] ?? null
        );

        if ($idCliente !== false) {
            return [
                'exito' => true,
                'mensaje' => 'Cliente registrado correctamente',
                'idCliente' => $idCliente
            ];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar el cliente'];
    }
}

try {
    $handler = new RegistrarClienteHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);