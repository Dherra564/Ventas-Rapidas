<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ComercianteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/ClienteController.php';
require_once __DIR__ . '/../../Aplicacion/Controladoras/UbicacionController.php';

$idComerciante = (int) ($_GET['id'] ?? 0);

try {
    $controlador = new ComercianteController();
    $comerciante = $controlador->buscar($idComerciante);

    if ($comerciante === null) {
        echo json_encode(['exito' => false, 'mensaje' => 'Comerciante no encontrado']);
        exit;
    }

    $ubicacionDatos = null;
    $cliente = (new ClienteController())->buscarPorIdUsuario($comerciante->getIdUsuario());
    if ($cliente !== null) {
        $ubicacion = (new UbicacionController())->buscarPorCliente($cliente->getIdCliente());
        if ($ubicacion !== null) {
            $ubicacionDatos = [
                'idProvincia' => $ubicacion->getIdProvincia(),
                'idCanton' => $ubicacion->getIdCanton(),
                'idDistrito' => $ubicacion->getIdDistrito(),
                'direccionExacta' => $ubicacion->getDireccionExacta(),
                'referencia' => $ubicacion->getReferencia()
            ];
        }
    }

    echo json_encode([
        'exito' => true,
        'comerciante' => [
            'idComerciante' => $comerciante->getIdComerciante(),
            'nombre' => $comerciante->getNombreCompleto(),
            'alias' => $comerciante->getAlias(),
            'numeroIdentificacion' => $comerciante->getNumeroIdentificacion(),
            'correo' => $comerciante->getCorreo(),
            'fotoPerfil' => $comerciante->getFotoPerfil(),
            'activo' => $comerciante->isActivo()
        ],
        'ubicacion' => $ubicacionDatos
    ]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}