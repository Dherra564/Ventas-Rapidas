<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class RegistrarProductoHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new ProductoController();

        $porcentajeDescuento = trim($_POST['porcentajeDescuento'] ?? '');

        $nombreImagen = $this->subirImagenPerfil($_FILES['imagen'] ?? null, 'producto');

        $idProducto = $controlador->registrar(
            (int) ($_POST['idLocal'] ?? 0),
            $_POST['nombreTipoProducto'] ?? '',
            $_POST['nombre'] ?? '',
            (float) ($_POST['precioOriginal'] ?? 0),
            $porcentajeDescuento !== '' ? (float) $porcentajeDescuento : null,
            $_POST['descripcion'] ?? null,
            (int) ($_POST['cantidadDisponible'] ?? 0),
            $nombreImagen !== false ? $nombreImagen : null
        );

        if ($idProducto !== false) {
            return [
                'exito' => true,
                'mensaje' => 'Producto registrado correctamente',
                'idProducto' => $idProducto
            ];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo registrar el producto'];
    }
}

try {
    $handler = new RegistrarProductoHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);