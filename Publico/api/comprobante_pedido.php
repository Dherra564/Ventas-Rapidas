<?php
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/FormateadorPedido.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

function e($texto): string
{
    return htmlspecialchars((string) ($texto ?? ''), ENT_QUOTES, 'UTF-8');
}

function colones(float $monto): string
{
    return '₡' . number_format($monto, 2, ',', ' ');
}

function paginaError(string $mensaje): void
{
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
        . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
        . '<title>Comprobante</title><link rel="stylesheet" href="../css/estilos.css"></head>'
        . '<body class="comprobante-pagina"><div class="comprobante-hoja comprobante-error">'
        . '<h2>No se pudo mostrar el comprobante</h2><p>' . e($mensaje) . '</p>'
        . '</div></body></html>';
    exit;
}

$usuario = Sesion::usuarioActual();

if ($usuario === null) {
    http_response_code(401);
    paginaError('Debes iniciar sesión para ver este comprobante.');
}

try {
    $idPedido = (int) ($_GET['idPedido'] ?? 0);
    $controlador = new PedidoController();
    $datos = $controlador->obtenerParaComprobante($usuario, $idPedido);
} catch (InvalidArgumentException $ex) {
    http_response_code(403);
    paginaError($ex->getMessage());
} catch (Throwable $ex) {
    http_response_code(500);
    paginaError('Error del servidor.');
}

$esCopiaCliente = $usuario['tipo'] !== Sesion::TIPO_SUPERADMIN
    && !$controlador->esDuenoDelLocal($usuario, $datos['pedido']);

$p = FormateadorPedido::aArreglo($datos, $esCopiaCliente);
$historial = $datos['historial'] ?? [];

$titulo = $p['estado'] === Pedido::ESTADO_ENTREGADO ? 'Comprobante de compra' : 'Comprobante de pedido';
$registroFecha = $p['registroFecha'] ? (new DateTime($p['registroFecha']))->format('d/m/Y h:i a') : '—';

$claseEstado = 'estado-pedido-' . strtolower($p['estado']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?> <?= e($p['numero']) ?></title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body class="comprobante-pagina">
    <div class="comprobante-acciones">
        <button type="button" class="comprobante-btn-imprimir" onclick="window.print()">Imprimir / Guardar PDF</button>
    </div>

    <div class="comprobante-hoja">
        <div class="comprobante-encabezado">
            <div class="comprobante-marca">Rapi<span>Ventas</span></div>
            <div>
                <h1 class="comprobante-titulo"><?= e($titulo) ?></h1>
                <div class="comprobante-numero"><?= e($p['numero']) ?></div>
            </div>
        </div>

        <div class="comprobante-datos">
            <div>
                <strong class="comprobante-dato-titulo">Local</strong>
                <?= e($p['localNombre']) ?><br>
                Tel. <?= e($p['localTelefono']) ?>
            </div>
            <div>
                <strong class="comprobante-dato-titulo">Cliente</strong>
                <?= e($p['clienteNombre']) ?><br>
                <?= e($p['clienteCorreo']) ?>
            </div>
            <div>
                <strong class="comprobante-dato-titulo">Fecha del pedido</strong>
                <?= e($registroFecha) ?>
            </div>
            <div>
                <strong class="comprobante-dato-titulo">Estado</strong>
                <span class="estado-pedido <?= e($claseEstado) ?>"><?= e($p['estado']) ?></span>
            </div>
        </div>

        <table class="comprobante-tabla">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="comprobante-num">Cant.</th>
                    <th class="comprobante-num">Precio unit.</th>
                    <th class="comprobante-num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($p['detalles'] as $d): ?>
                    <tr>
                        <td>
                            <?= e($d['productoNombre']) ?>
                            <?php if ($d['descuentoPorcentaje']): ?>
                                <span class="comprobante-descuento">
                                    -<?= e(rtrim(rtrim(number_format($d['descuentoPorcentaje'], 2), '0'), '.')) ?>% de descuento
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="comprobante-num"><?= (int) $d['cantidad'] ?></td>
                        <td class="comprobante-num">
                            <?php if ($d['descuentoPorcentaje']): ?>
                                <span class="comprobante-tachado"><?= colones($d['precio']) ?></span>
                            <?php endif; ?>
                            <?= colones($d['precioUnitario']) ?>
                        </td>
                        <td class="comprobante-num"><?= colones($d['subtotal']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="comprobante-total">
            <span>Total</span>
            <span><?= colones($p['total']) ?></span>
        </div>

        <?php if ($p['retiroCodigo'] && $p['estado'] === Pedido::ESTADO_CONFIRMADO): ?>
            <div class="comprobante-codigo">
                <span class="comprobante-codigo-etiqueta">Código de retiro — muéstralo en el local</span>
                <span class="comprobante-codigo-valor"><?= e($p['retiroCodigo']) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($p['motivo'] && in_array($p['estado'], [Pedido::ESTADO_RECHAZADO, Pedido::ESTADO_CANCELADO], true)): ?>
            <div class="comprobante-motivo"><strong>Motivo:</strong> <?= e($p['motivo']) ?></div>
        <?php endif; ?>

        <?php if (!empty($historial)): ?>
            <h3 class="comprobante-subtitulo">Historial del pedido</h3>
            <ul class="comprobante-historial">
                <?php foreach (array_reverse($historial) as $h): ?>
                    <li>
                        <?= e($h->getFecha()?->format('d/m/Y h:i a')) ?> —
                        <?= $h->getValorAnterior() ? e($h->getValorAnterior()) . ' → ' : '' ?><?= e($h->getValorNuevo()) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p class="comprobante-pie">
            Comprobante generado por RapiVentas el <?= e((new DateTime())->format('d/m/Y h:i a')) ?>.<br>
            Este documento no sustituye una factura electrónica.
        </p>
    </div>
</body>
</html>