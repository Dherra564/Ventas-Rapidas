<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProveedorController.php';

$cedula = trim($_GET['cedula'] ?? '');

try {
    $controlador = new ProveedorController();
    $proveedor = $controlador->buscarPorCedula($cedula);

    if ($proveedor === null) {
        echo json_encode(['encontrado' => false]);
        exit;
    }

    echo json_encode([
        'encontrado' => true,
        'idProveedor' => $proveedor->getIdProveedor(),
        'nombre' => $proveedor->getNombre(),
        'apellido' => $proveedor->getApellido()
    ]);

} catch (Exception $e) {
    echo json_encode(['encontrado' => false]);
}