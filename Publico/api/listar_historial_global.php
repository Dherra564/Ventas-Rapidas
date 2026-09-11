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

    $union = "
        SELECT CONCAT('cp-', h.tbcomerciantepasswordhistoricoid) AS id, 'password' AS tipo, 'Comerciante' AS entidadTipo, c.tbcomerciantenombre AS usuarioNombre, h.valoranterior AS valorAnterior, h.valornuevo AS valorNuevo, h.fecha AS fecha
        FROM tbcomerciantepasswordhistorico h JOIN tbcomerciante c ON c.tbcomercianteid = h.tbcomercianteid

        UNION ALL
        SELECT CONCAT('clp-', h.tbclientepasswordhistoricoid), 'password', 'Cliente', c.tbclientenombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbclientepasswordhistorico h JOIN tbcliente c ON c.tbclienteid = h.tbclienteid

        UNION ALL
        SELECT CONCAT('cf-', h.tbcomercianteperfilimagenhistoricoid), 'perfilImagen', 'Comerciante', c.tbcomerciantenombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tbcomercianteperfilimagenhistorico h JOIN tbcomerciante c ON c.tbcomercianteid = h.tbcomercianteid

        UNION ALL
        SELECT CONCAT('clf-', h.tbclienteperfilimagenhistoricoid), 'perfilImagen', 'Cliente', c.tbclientenombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbclienteperfilimagenhistorico h JOIN tbcliente c ON c.tbclienteid = h.tbclienteid

        UNION ALL
        SELECT CONCAT('cn-', h.tbcomerciantenombrehistoricoid), 'nombre', 'Comerciante', c.tbcomerciantenombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tbcomerciantenombrehistorico h JOIN tbcomerciante c ON c.tbcomercianteid = h.tbcomercianteid

        UNION ALL
        SELECT CONCAT('cln-', h.tbclientenombrecompletohistoricoid), 'nombre', 'Cliente', c.tbclientenombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbclientenombrecompletohistorico h JOIN tbcliente c ON c.tbclienteid = h.tbclienteid

        UNION ALL
        SELECT CONCAT('cc-', h.tbcomerciantecorreohistoricoid), 'correo', 'Comerciante', c.tbcomerciantenombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tbcomerciantecorreohistorico h JOIN tbcomerciante c ON c.tbcomercianteid = h.tbcomercianteid

        UNION ALL
        SELECT CONCAT('clc-', h.tbclientecorreohistoricoid), 'correo', 'Cliente', c.tbclientenombrecompleto, h.valoranterior, h.valornuevo, h.fecha
        FROM tbclientecorreohistorico h JOIN tbcliente c ON c.tbclienteid = h.tbclienteid

        UNION ALL
        SELECT CONCAT('ln-', h.tblocalnombrehistoricoid), 'nombre', 'Local', l.tblocalnombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocalnombrehistorico h JOIN tblocal l ON l.tblocalid = h.tblocalid

        UNION ALL
        SELECT CONCAT('lt-', h.tblocaltelefonohistoricoid), 'telefono', 'Local', l.tblocalnombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocaltelefonohistorico h JOIN tblocal l ON l.tblocalid = h.tblocalid

        UNION ALL
        SELECT CONCAT('ll-', h.tblocallogohistoricoid), 'logo', 'Local', l.tblocalnombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tblocallogohistorico h JOIN tblocal l ON l.tblocalid = h.tblocalid

        UNION ALL
        SELECT CONCAT('pp-', h.tbproductopreciohistoricoid), 'precio', 'Producto', p.tbproductonombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tbproductopreciohistorico h JOIN tbproducto p ON p.tbproductoid = h.tbproductoid

        UNION ALL
        SELECT CONCAT('pd-', h.tbproductodescuentoporcentajehistoricoid), 'descuento', 'Producto', p.tbproductonombre, h.valoranterior, h.valornuevo, h.fecha
        FROM tbproductodescuentoporcentajehistorico h JOIN tbproducto p ON p.tbproductoid = h.tbproductoid
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
        $condiciones[] = "usuarioNombre LIKE :termino";
        $parametros[':termino'] = '%' . $termino . '%';
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
        $condicionesConteo[] = "usuarioNombre LIKE :termino";
        $parametrosConteo[':termino'] = '%' . $termino . '%';
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