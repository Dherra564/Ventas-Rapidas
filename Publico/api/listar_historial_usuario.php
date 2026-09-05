<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/HistorialController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/PasswordHistorial.php';

try {
    $idUsuario = (int) ($_GET['idUsuario'] ?? 0);
    $tipoUsuario = trim($_GET['tipoUsuario'] ?? '');

    if ($idUsuario <= 0 || !in_array($tipoUsuario, ['Cliente', 'Comerciante'], true)) {
        throw new InvalidArgumentException('Selecciona un usuario válido');
    }

    $controlador = new HistorialController();
    $passwords = array_map(fn($h) => [
        'idHistorial' => $h->getIdHistorialPassword(),
        'fecha' => $h->getFechaCambio()?->format('Y-m-d H:i:s'),
        'exitoso' => $h->isExitoso()
    ], $controlador->listarPasswords($idUsuario, $tipoUsuario));

    $fotos = array_map(fn($h) => [
        'idHistorial' => $h->getIdHistorialFotoPerfil(),
        'fecha' => $h->getFechaCambio()?->format('Y-m-d H:i:s'),
        'rutaAnterior' => $h->getRutaAnterior(),
        'rutaNueva' => $h->getRutaNueva()
    ], $controlador->listarFotos($idUsuario, $tipoUsuario));

    echo json_encode(['exito' => true, 'passwords' => $passwords, 'fotos' => $fotos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'passwords' => [], 'fotos' => []]);
}
