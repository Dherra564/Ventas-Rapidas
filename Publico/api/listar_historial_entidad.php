<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/HistorialController.php';
require_once __DIR__ . '/../../Configuracion/BaseDatos.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $entidad = trim($_GET['entidad'] ?? '');
    $idEntidad = (int) ($_GET['idEntidad'] ?? 0);

    if (!in_array($entidad, ['Comerciante', 'Cliente', 'Local'], true) || $idEntidad <= 0) {
        throw new InvalidArgumentException('Parámetros inválidos');
    }

    $controlador = new HistorialController();

    if ($entidad === 'Local') {
        $campos = ['nombre', 'telefono', 'logo'];
    } else {
        $campos = ['nombre', 'correo', 'perfilImagen', 'password'];
    }

    $datos = [];
    foreach ($campos as $campo) {
        $registros = $controlador->listarHistorial($entidad, $campo, $idEntidad);
        $datos[$campo] = array_map(fn($h) => [
            'valorAnterior' => $h->getValorAnterior(),
            'valorNuevo' => $h->getValorNuevo(),
            'fecha' => $h->getFecha()?->format('Y-m-d H:i:s')
        ], $registros);
    }

    if ($entidad === 'Local') {
        $conexion = BaseDatos::obtenerConexion();
        $consulta = $conexion->prepare("SELECT tbubicacionid FROM tbubicacion WHERE tblocalid = :id LIMIT 1");
        $consulta->execute([':id' => $idEntidad]);
        $idUbicacion = $consulta->fetchColumn();

        if ($idUbicacion) {
            foreach (['provincia', 'canton', 'distrito', 'direccionExacta'] as $campo) {
                $registros = $controlador->listarHistorial('Ubicacion', $campo, (int) $idUbicacion);
                $datos[$campo] = array_map(fn($h) => [
                    'valorAnterior' => $h->getValorAnterior(),
                    'valorNuevo' => $h->getValorNuevo(),
                    'fecha' => $h->getFecha()?->format('Y-m-d H:i:s')
                ], $registros);
            }
        }
    }

    echo json_encode(['exito' => true, 'campos' => $datos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}