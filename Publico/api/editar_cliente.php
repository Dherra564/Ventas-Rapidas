<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Cliente.php';

try {
    $controlador = new ClienteController();

    $idCliente = (int) ($_POST['idCliente'] ?? 0);
    $actual = $controlador->buscar($idCliente);

    if ($actual === null) {
        throw new InvalidArgumentException('Cliente no encontrado');
    }

    $correoNuevo = trim($_POST['correo'] ?? '');
    if ($correoNuevo !== $actual->getCorreo() && $controlador->existeCorreo($correoNuevo)) {
        throw new InvalidArgumentException('Ese correo ya está en uso por otro cliente');
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
        $actual->getPerfilImagen(),
        $actual->isActivo(),
        $idCliente
    );

    $actualizado = $controlador->editar($cliente);

    if ($actualizado && isset($_FILES['fotoPerfil']) && ($_FILES['fotoPerfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $fotoNueva = $controlador->cambiarFotoPerfil($idCliente, $_FILES['fotoPerfil']);
        if ($fotoNueva === false) {
            throw new Exception('Los datos se actualizaron, pero no se pudo cambiar la foto');
        }
    }

    echo json_encode([
        'exito' => $actualizado,
        'mensaje' => $actualizado ? 'Cliente actualizado correctamente' : 'No se pudo actualizar el cliente'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}
