<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Cliente.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class EditarClienteHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new ClienteController();

        $idCliente = (int) ($_POST['idCliente'] ?? 0);
        $actual = $controlador->buscar($idCliente);

        if ($actual === null) {
            return ['exito' => false, 'mensaje' => 'Cliente no encontrado'];
        }

        $correoNuevo = trim($_POST['correo'] ?? '');
        if ($correoNuevo !== $actual->getCorreo() && $controlador->existeCorreo($correoNuevo)) {
            return ['exito' => false, 'mensaje' => 'Ese correo ya está en uso por otro cliente'];
        }

        $nombreFotoNueva = $this->subirImagenPerfil($_FILES['fotoPerfil'] ?? null, 'cliente');

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

        $cliente = new Cliente(
            $_POST['nombreCompleto'] ?? '',
            $actual->getNumeroIdentificacion(),
            $correoNuevo,
            $passwordHash,
            $fotoFinal,
            true,
            $idCliente
        );

        $actualizado = $controlador->editar($cliente);

        return [
            'exito' => $actualizado,
            'mensaje' => $actualizado ? 'Cliente actualizado correctamente' : 'No se pudo actualizar el cliente'
        ];
    }
}

try {
    $handler = new EditarClienteHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);