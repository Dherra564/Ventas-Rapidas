<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Comerciante.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion(Sesion::TIPO_COMERCIANTE);

try {
    $controlador = new ComercianteController();

    $idComerciante = (int) ($_POST['idComerciante'] ?? 0);

    if ($idComerciante !== $usuario['id']) {
        http_response_code(403);
        echo json_encode(['exito' => false, 'mensaje' => 'No tienes permiso para editar esa cuenta']);
        exit;
    }

    $actual = $controlador->buscar($idComerciante);

    if ($actual === null) {
        throw new InvalidArgumentException('Comerciante no encontrado');
    }

    $correoNuevo = trim($_POST['correo'] ?? '');
    if ($correoNuevo !== $actual->getCorreo() && $controlador->existeCorreo($correoNuevo)) {
        throw new InvalidArgumentException('Ese correo ya está en uso por otro comerciante');
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
        $actual->getPerfilImagen(),
        $actual->isActivo(),
        $idComerciante,
        $actual->getFechaRegistro()
    );

    $actualizado = $controlador->editar($comerciante);

    if ($actualizado && isset($_FILES['fotoPerfil']) && ($_FILES['fotoPerfil']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $fotoNueva = $controlador->cambiarFotoPerfil($idComerciante, $_FILES['fotoPerfil']);
        if ($fotoNueva === false) {
            throw new Exception('Los datos se actualizaron, pero no se pudo cambiar la foto');
        }
    }

    echo json_encode([
        'exito' => $actualizado,
        'mensaje' => $actualizado ? 'Comerciante actualizado correctamente' : 'No se pudo actualizar el comerciante'
    ]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
}