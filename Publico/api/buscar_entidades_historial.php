<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Configuracion/BaseDatos.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $termino = trim($_GET['termino'] ?? '');

    if ($termino === '') {
        echo json_encode(['exito' => true, 'resultados' => []]);
        exit;
    }

    $conexion = BaseDatos::obtenerConexion();
    $like = '%' . $termino . '%';
    $resultados = [];

    $sqlComerciantes = "SELECT tbcomercianteid AS id, tbcomerciantenombre AS nombre, tbcomerciantecorreo AS correo
                         FROM tbcomerciante
                         WHERE tbcomerciantenombre LIKE :t1
                            OR tbcomerciantecorreo LIKE :t2
                            OR tbcomercianteidentificacionnumero LIKE :t3
                         LIMIT 10";
    $consulta = $conexion->prepare($sqlComerciantes);
    $consulta->execute([':t1' => $like, ':t2' => $like, ':t3' => $like]);
    while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $resultados[] = [
            'tipo' => 'Comerciante',
            'id' => (int) $fila['id'],
            'etiqueta' => "{$fila['nombre']} ({$fila['correo']})"
        ];
    }

    $sqlClientes = "SELECT tbclienteid AS id, tbclientenombrecompleto AS nombre, tbclientecorreo AS correo
                     FROM tbcliente
                     WHERE tbclientenombrecompleto LIKE :t1
                        OR tbclientecorreo LIKE :t2
                        OR tbclienteidentificacionnumero LIKE :t3
                     LIMIT 10";
    $consulta = $conexion->prepare($sqlClientes);
    $consulta->execute([':t1' => $like, ':t2' => $like, ':t3' => $like]);
    while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $resultados[] = [
            'tipo' => 'Cliente',
            'id' => (int) $fila['id'],
            'etiqueta' => "{$fila['nombre']} ({$fila['correo']})"
        ];
    }

    $sqlLocales = "SELECT tblocalid AS id, tblocalnombre AS nombre
                    FROM tblocal
                    WHERE tblocalnombre LIKE :t1
                    LIMIT 10";
    $consulta = $conexion->prepare($sqlLocales);
    $consulta->execute([':t1' => $like]);
    while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $resultados[] = [
            'tipo' => 'Local',
            'id' => (int) $fila['id'],
            'etiqueta' => $fila['nombre']
        ];
    }

    echo json_encode(['exito' => true, 'resultados' => $resultados]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}