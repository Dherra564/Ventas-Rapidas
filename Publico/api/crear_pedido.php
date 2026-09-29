<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/PedidoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/FormateadorPedido.php';
require_once __DIR__ . '/../../Aplicacion/Comun/LectorPagoSinpe.php';
require_once __DIR__ . '/../../Aplicacion/Comun/Sesion.php';

$usuario = Sesion::requerirSesion();

class CrearPedidoHandler
{
    use LectorPagoSinpe;

    public function manejar(array $usuario): array
    {
        $idLocal = (int) ($_POST['idLocal'] ?? 0);
        $items = json_decode($_POST['items'] ?? '[]', true);
        $items = is_array($items) ? $items : [];

        $pago = null;

        try {
            $pago = $this->leerPagoSinpe();

            $controlador = new PedidoController();
            $resultado = $controlador->crearPedido($usuario, $idLocal, $items, $pago);
        } catch (Throwable $e) {
            $this->descartarComprobante($pago);
            throw $e;
        }

        return [
            'exito' => true,
            'mensaje' => 'Pedido enviado. El local debe confirmar que le llegó el SINPE.',
            'idPedido' => $resultado['idPedido'],
            'numero' => FormateadorPedido::numero($resultado['idPedido']),
            'total' => $resultado['total']
        ];
    }
}

try {
    $handler = new CrearPedidoHandler();
    echo json_encode($handler->manejar($usuario));
} catch (InvalidArgumentException $e) {
    echo json_encode(['exito' => false, 'mensaje' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['exito' => false, 'mensaje' => 'No se pudo generar el pedido: ' . $e->getMessage()]);
}