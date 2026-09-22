<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Configuracion/BaseDatos.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

Sesion::requerirSesion(Sesion::TIPO_SUPERADMIN);

try {
    $tipo = trim($_GET['tipo'] ?? 'todos');
    $entidadTipo = trim($_GET['entidadTipo'] ?? 'todos');
    $termino = trim($_GET['termino'] ?? '');

    $conexion = BaseDatos::obtenerConexion();

    $esComercianteCase = "CASE WHEN EXISTS (
        SELECT 1 FROM tbcomerciante co
        WHERE co.tbusuarioid = u.tbusuarioid AND co.tbcomercianteactivo = 1
    ) THEN 'Comerciante' ELSE 'Cliente' END";

    $union = "
        SELECT CONCAT('up-', h.tbusuariopasswordhistoricoid) AS id, 'password' AS tipo, {$esComercianteCase} AS entidadTipo, u.tbusuarionombrecompleto AS usuarioNombre, NULL AS localNombre, NULL AS autorNombre, h.valoranterior AS valorAnterior, h.valornuevo AS valorNuevo, h.fecha AS fecha
        FROM tbusuariopasswordhistorico h JOIN tbusuario u ON u.tbusuarioid = h.tbusuarioid

        UNION ALL
        SELECT CONCAT('uf-', h.tbusuarioperfilimagenhistoricoid), 'perfilImagen', {$esComercianteCase}, u.tbusuarionombrecompleto, NULL, NULL, h.valoranterior, h.valornuevo, h.fecha
        FROM tbusuarioperfilimagenhistorico h JOIN tbusuario u ON u.tbusuarioid = h.tbusuarioid

        UNION ALL
        SELECT CONCAT('un-', h.tbusuarionombrecompletohistoricoid), 'nombre', {$esComercianteCase}, u.tbusuarionombrecompleto, NULL, NULL, h.valoranterior, h.valornuevo, h.fecha
        FROM tbusuarionombrecompletohistorico h JOIN tbusuario u ON u.tbusuarioid = h.tbusuarioid

        UNION ALL
        SELECT CONCAT('uc-', h.tbusuariocorreohistoricoid), 'correo', {$esComercianteCase}, u.tbusuarionombrecompleto, NULL, NULL, h.valoranterior, h.valornuevo, h.fecha
        FROM tbusuariocorreohistorico h JOIN tbusuario u ON u.tbusuarioid = h.tbusuarioid

        UNION ALL
        SELECT CONCAT('ln-', h.tblocalnombrehistoricoid), 'nombre', 'Local', l.tblocalnombre, NULL, au.tbusuarionombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocalnombrehistorico h
        JOIN tblocal l ON l.tblocalid = h.tblocalid
        LEFT JOIN tbusuario au ON au.tbusuarioid = h.idusuario

        UNION ALL
        SELECT CONCAT('lt-', h.tblocaltelefonohistoricoid), 'telefono', 'Local', l.tblocalnombre, NULL, au.tbusuarionombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocaltelefonohistorico h
        JOIN tblocal l ON l.tblocalid = h.tblocalid
        LEFT JOIN tbusuario au ON au.tbusuarioid = h.idusuario

        UNION ALL
        SELECT CONCAT('ll-', h.tblocallogohistoricoid), 'logo', 'Local', l.tblocalnombre, NULL, au.tbusuarionombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocallogohistorico h
        JOIN tblocal l ON l.tblocalid = h.tblocalid
        LEFT JOIN tbusuario au ON au.tbusuarioid = h.idusuario

        UNION ALL
        SELECT CONCAT('pp-', h.tbproductopreciohistoricoid), 'precio', 'Producto', p.tbproductonombre, l2.tblocalnombre, au.tbusuarionombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbproductopreciohistorico h
        JOIN tbproducto p ON p.tbproductoid = h.tbproductoid
        JOIN tblocal l2 ON l2.tblocalid = p.tblocalid
        LEFT JOIN tbusuario au ON au.tbusuarioid = h.idusuario

        UNION ALL
        SELECT CONCAT('pd-', h.tbproductodescuentoporcentajehistoricoid), 'descuento', 'Producto', p.tbproductonombre, l2.tblocalnombre, au.tbusuarionombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbproductodescuentoporcentajehistorico h
        JOIN tbproducto p ON p.tbproductoid = h.tbproductoid
        JOIN tblocal l2 ON l2.tblocalid = p.tblocalid
        LEFT JOIN tbusuario au ON au.tbusuarioid = h.idusuario
    ";

    $condiciones = [];
    $parametros = [];

    if ($tipo !== '' && $tipo !== 'todos') {
        $condiciones[] = "tipo = :tipo";
        $parametros[':tipo'] = $tipo;
    }
    if ($entidadTipo !== '' && $entidadTipo !== 'todos') {
        $condiciones[] = "entidadTipo = :entidadTipo";
        $parametros[':entidadTipo'] = $entidadTipo;
    }
    if ($termino !== '') {
        $condiciones[] = "(usuarioNombre LIKE :termino OR localNombre LIKE :termino2 OR autorNombre LIKE :termino3)";
        $parametros[':termino'] = '%' . $termino . '%';
        $parametros[':termino2'] = '%' . $termino . '%';
        $parametros[':termino3'] = '%' . $termino . '%';
    }

    $sqlListado = "SELECT * FROM ({$union}) AS todo";
    if (!empty($condiciones)) {
        $sqlListado .= " WHERE " . implode(" AND ", $condiciones);
    }
    $limite = isset($_GET['limite']) ? max(1, min(150, (int) $_GET['limite'])) : 150;
    $sqlListado .= " ORDER BY fecha DESC LIMIT {$limite}";

    $consulta = $conexion->prepare($sqlListado);
    $consulta->execute($parametros);
    $registros = $consulta->fetchAll(PDO::FETCH_ASSOC);

    $condicionesConteo = [];
    $parametrosConteo = [];
    if ($entidadTipo !== '' && $entidadTipo !== 'todos') {
        $condicionesConteo[] = "entidadTipo = :entidadTipo";
        $parametrosConteo[':entidadTipo'] = $entidadTipo;
    }
    if ($termino !== '') {
        $condicionesConteo[] = "(usuarioNombre LIKE :termino OR localNombre LIKE :termino2 OR autorNombre LIKE :termino3)";
        $parametrosConteo[':termino'] = '%' . $termino . '%';
        $parametrosConteo[':termino2'] = '%' . $termino . '%';
        $parametrosConteo[':termino3'] = '%' . $termino . '%';
    }

    $sqlConteos = "SELECT tipo, COUNT(*) AS cantidad FROM ({$union}) AS todo";
    if (!empty($condicionesConteo)) {
        $sqlConteos .= " WHERE " . implode(" AND ", $condicionesConteo);
    }
    $sqlConteos .= " GROUP BY tipo";

    $consultaConteos = $conexion->prepare($sqlConteos);
    $consultaConteos->execute($parametrosConteo);

    $conteos = ['todos' => 0];
    while ($fila = $consultaConteos->fetch(PDO::FETCH_ASSOC)) {
        $conteos[$fila['tipo']] = (int) $fila['cantidad'];
        $conteos['todos'] += (int) $fila['cantidad'];
    }

    echo json_encode(['exito' => true, 'registros' => $registros, 'conteos' => $conteos]);
} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
}