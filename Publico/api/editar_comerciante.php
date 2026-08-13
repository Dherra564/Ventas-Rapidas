<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Comerciante.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class EditarComercianteHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new ComercianteController();

        $idComerciante = (int) ($_POST['idComerciante'] ?? 0);
        $actual = $controlador->buscar($idComerciante);

        if ($actual === null) {
            return ['exito' => false, 'mensaje' => 'Comerciante no encontrado'];
        }

        $correoNuevo = trim($_POST['correo'] ?? '');
        if ($correoNuevo !== $actual->getCorreo() && $controlador->existeCorreo($correoNuevo)) {
            return ['exito' => false, 'mensaje' => 'Ese correo ya está en uso por otro comerciante'];
        }

        $nombreFotoNueva = $this->subirImagenPerfil($_FILES['fotoPerfil'] ?? null, 'comerciante');

        if ($nombreFotoNueva !== false) {
            $this->eliminarImagen($actual->getFotoPerfil());
            $fotoFinal = $nombreFotoNueva;
        } else {
            $fotoFinal = $actual->getFotoPerfil();
        }

        $passwordNueva = $_POST['password'] ?? '';
        $passwordHash = $passwordNueva !== ''
            ? password_hash($passwordNueva, PASSWORD_DEFAULT)
            : $actual->getPasswordHash();

        $comerciante = new Comerciante(
            $_POST['nombre'] ?? '',
            $_POST['alias'] ?? '',
            $actual->getNumeroIdentificacion(),
            $correoNuevo,
            $passwordHash,
            $fotoFinal,
            true,
            $idComerciante
        );

        $actualizado = $controlador->editar($comerciante);

        return [
            'exito' => $actualizado,
            'mensaje' => $actualizado ? 'Comerciante actualizado correctamente' : 'No se pudo actualizar el comerciante'
        ];
    }
}

try {
    $handler = new EditarComercianteHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);