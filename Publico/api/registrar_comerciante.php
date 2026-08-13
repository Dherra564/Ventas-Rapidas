<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ValidadorIdentificacion.php';

class RegistrarComercianteHandler
{
    use ManejadorImagenes, ValidadorIdentificacion;

    public function manejar(): array
    {
        $controlador = new ComercianteController();

        $tipoIdentificacion = $_POST['tipoIdentificacion'] ?? '';
        $numeroIdentificacion = trim($_POST['numeroIdentificacion'] ?? '');
        $correo = trim($_POST['correo'] ?? '');

        $this->validarIdentificacion($tipoIdentificacion, $numeroIdentificacion);

        if ($controlador->existeIdentificacion($numeroIdentificacion)) {
            return ['exito' => false, 'mensaje' => 'Ese número de identificación ya está registrado'];
        }

        if ($controlador->existeCorreo($correo)) {
            return ['exito' => false, 'mensaje' => 'Ese correo ya está registrado'];
        }

        $nombreImagen = $this->subirImagenPerfil($_FILES['fotoPerfil'] ?? null, 'comerciante');

        $idComerciante = $controlador->registrar(
            $_POST['nombre'] ?? '',
            $_POST['alias'] ?? '',
            $numeroIdentificacion,
            $correo,
            $_POST['password'] ?? '',
            $nombreImagen !== false ? $nombreImagen : ''
        );

        if ($idComerciante !== false) {
            return [
                'exito' => true,
                'mensaje' => 'Comerciante registrado correctamente',
                'idComerciante' => $idComerciante
            ];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar el comerciante'];
    }
}

try {
    $handler = new RegistrarComercianteHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);