<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../Aplicacion/Controladoras/ProductoController.php';
require_once __DIR__ . '/../../Aplicacion/Modelos/Producto.php';
require_once __DIR__ . '/../../Aplicacion/Comun/ManejadorImagenes.php';

class EditarProductoHandler
{
    use ManejadorImagenes;

    public function manejar(): array
    {
        $controlador = new ProductoController();

        $idProducto = (int) ($_POST['idProducto'] ?? 0);
        $productoActual = $controlador->buscar($idProducto);

        if ($productoActual === null) {
            return ['exito' => false, 'mensaje' => 'Producto no encontrado'];
        }

        $idTipoProducto = $controlador->resolverTipoProducto($_POST['nombreTipoProducto'] ?? '');

        $porcentajeDescuento = trim($_POST['porcentajeDescuento'] ?? '');
        $nombreImagenNueva = $this->subirImagenPerfil($_FILES['imagen'] ?? null, 'producto');

        if ($nombreImagenNueva !== false) {
            $this->eliminarImagen($productoActual->getImagen());
            $imagenFinal = $nombreImagenNueva;
        } else {
            $imagenFinal = $productoActual->getImagen();
        }

        $producto = new Producto(
            $productoActual->getIdLocal(),
            $idTipoProducto,
            $_POST['nombre'] ?? '',
            (float) ($_POST['precioOriginal'] ?? 0),
            $porcentajeDescuento !== '' ? (float) $porcentajeDescuento : null,
            $_POST['descripcion'] ?? null,
            (int) ($_POST['cantidadDisponible'] ?? 0),
            $imagenFinal,
            true,
            $idProducto
        );

        $actualizado = $controlador->editar($producto);

        return [
            'exito' => $actualizado,
            'mensaje' => $actualizado ? 'Producto actualizado correctamente' : 'No se pudo actualizar el producto'
        ];
    }
}

try {
    $handler = new EditarProductoHandler();
    $respuesta = $handler->manejar();
} catch (InvalidArgumentException $e) {
    $respuesta = ['exito' => false, 'mensaje' => $e->getMessage()];
} catch (Exception $e) {
    $respuesta = ['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()];
}

echo json_encode($respuesta);