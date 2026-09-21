<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/HistorialController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';

try {
    $id = (int) ($_GET['idUsuario'] ?? 0);
    $tipoUsuario = trim($_GET['tipoUsuario'] ?? '');

    if ($id <= 0 || !in_array($tipoUsuario, ['Cliente', 'Comerciante'], true)) {
        throw new InvalidArgumentException('Selecciona un usuario válido');
    }

    $persona = $tipoUsuario === 'Cliente'
        ? (new ClienteController())->buscar($id)
        : (new ComercianteController())->buscar($id);

    if ($persona === null) {
        throw new InvalidArgumentException('No se encontró ese usuario');
    }

    $idUsuario = $persona->getIdUsuario();

    $controlador = new HistorialController();

    $passwords = array_map(fn($h) => [
        'idHistorial' => $h->getIdHistorial(),
        'fecha' => $h->getFecha()?->format('Y-m-d H:i:s')

    ], $controlador->listarPasswords($idUsuario, $tipoUsuario));

    $fotos = array_map(fn($h) => [
        'idHistorial' => $h->getIdHistorial(),
        'fecha' => $h->getFecha()?->format('Y-m-d H:i:s'),
        'rutaAnterior' => $h->getValorAnterior(),
        'rutaNueva' => $h->getValorNuevo()
    ], $controlador->listarFotos($idUsuario, $tipoUsuario));

    echo json_encode(['exito' => true, 'passwords' => $passwords, 'fotos' => $fotos]);
} catch (Throwable $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage(), 'passwords' => [], 'fotos' => []]);
}