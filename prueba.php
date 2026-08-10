<?php

require_once __DIR__ . "/Aplicacion/Controladoras/ComercianteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/LocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProductoController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProvinciaController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/CantonController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/DistritoController.php";

$comercianteController = new ComercianteController();
$localController = new LocalController();
$productoController = new ProductoController();
$provinciaController = new ProvinciaController();
$cantonController = new CantonController();
$distritoController = new DistritoController();

function leer(string $etiqueta): string
{
    echo $etiqueta;
    return trim(fgets(STDIN));
}

function leerOpcional(string $etiqueta): ?string
{
    $valor = leer($etiqueta);
    return $valor === "" ? null : $valor;
}

function elegirUbicacionEnCascada(
    ProvinciaController $provinciaController,
    CantonController $cantonController,
    DistritoController $distritoController
): array {

    echo "\n--- Elegir provincia ---\n";
    foreach ($provinciaController->listar() as $provincia) {
        echo "  {$provincia->getIdProvincia()}. {$provincia->getNombre()}\n";
    }
    $idProvincia = (int) leer("ID de provincia: ");

    echo "\n--- Elegir cantón (de esa provincia) ---\n";
    $cantones = $cantonController->listarPorProvincia($idProvincia);
    if (empty($cantones)) {
        echo "  (No hay cantones registrados para esa provincia)\n";
    }
    foreach ($cantones as $canton) {
        echo "  {$canton->getIdCanton()}. {$canton->getNombre()}\n";
    }
    $idCanton = (int) leer("ID de cantón: ");

    echo "\n--- Elegir distrito (de ese cantón) ---\n";
    $distritos = $distritoController->listarPorCanton($idCanton);
    if (empty($distritos)) {
        echo "  (No hay distritos registrados para ese cantón)\n";
    }
    foreach ($distritos as $distrito) {
        echo "  {$distrito->getIdDistrito()}. {$distrito->getNombre()}\n";
    }
    $idDistrito = (int) leer("ID de distrito: ");

    return [$idProvincia, $idCanton, $idDistrito];
}

do {

    echo "\n=========================\n";
    echo "   SISTEMA DE PRUEBA\n";
    echo "=========================\n";
    echo " 1. Registrar comerciante\n";
    echo " 2. Listar comerciantes\n";
    echo " 3. Buscar comerciantes (filtros)\n";
    echo " 4. Registrar local\n";
    echo " 5. Listar locales\n";
    echo " 6. Buscar local por ID (con ubicación)\n";
    echo " 7. Buscar locales (filtros, incluye ubicación)\n";
    echo " 8. Eliminar local\n";
    echo " 9. Registrar producto\n";
    echo "10. Listar productos\n";
    echo "11. Buscar productos (filtros)\n";
    echo "12. Eliminar producto\n";
    echo " 0. Salir\n";
    $opcion = leer("Seleccione una opción: ");

    switch ($opcion) {

        case "1":

            $nombreCompleto = leer("\nNombre completo: ");
            $alias = leer("Alias: ");
            $cedula = leer("Cédula: ");
            $correo = leer("Correo: ");
            $password = leer("Password: ");

            try {
                $id = $comercianteController->registrar($nombreCompleto, $alias, $cedula, $correo, $password);
                echo $id ? "\nComerciante registrado con ID $id\n" : "\nError al registrar comerciante\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "2":

            $comerciantes = $comercianteController->listar();

            echo "\n------ COMERCIANTES ------\n";
            foreach ($comerciantes as $comerciante) {
                echo "ID: " . $comerciante->getIdComerciante() . "\n";
                echo "Nombre: " . $comerciante->getNombreCompleto() . "\n";
                echo "Alias: " . $comerciante->getAlias() . "\n";
                echo "Correo: " . $comerciante->getCorreo() . "\n";
                echo "--------------------\n";
            }

            break;

        case "3":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $alias = leerOpcional("Filtrar por alias (Enter para omitir): ");
            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $comercianteController->buscarConFiltros($nombre, $alias, $activo);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $comerciante) {
                echo "ID: " . $comerciante->getIdComerciante() . "\n";
                echo "Nombre: " . $comerciante->getNombreCompleto() . "\n";
                echo "Alias: " . $comerciante->getAlias() . "\n";
                echo "Activo: " . ($comerciante->isActivo() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "4":

            $idComerciante = (int) leer("\nID del comerciante: ");

            echo "\n--- Tipo de local (autocompletado) ---\n";
            $textoParcial = leerOpcional("Escriba parte del tipo de local para ver sugerencias (Enter para omitir): ");

            if ($textoParcial !== null) {
                $sugerencias = $localController->buscarTiposCoincidentes($textoParcial);
                if (empty($sugerencias)) {
                    echo "  (Sin coincidencias — se creará como tipo nuevo)\n";
                } else {
                    echo "  Coincidencias encontradas:\n";
                    foreach ($sugerencias as $tipo) {
                        echo "    - {$tipo->getNombre()}\n";
                    }
                }
            }

            $nombreTipoLocal = leer("Escriba el tipo de local definitivo (existente o nuevo): ");

            $nombreLocal = leer("Nombre local: ");
            $telefono = leer("Teléfono: ");
            $correo = leer("Correo: ");
            $descripcion = leerOpcional("Descripción (opcional, Enter para omitir): ");
            $productos = leerOpcional("Productos a ofrecer (opcional, Enter para omitir): ");
            $logo = leerOpcional("Logo (opcional, Enter para omitir): ");

            [$idProvincia, $idCanton, $idDistrito] = elegirUbicacionEnCascada(
                $provinciaController,
                $cantonController,
                $distritoController
            );

            $direccion = leer("Dirección exacta: ");
            $referencia = leerOpcional("Referencia (opcional, Enter para omitir): ");

            try {
                $resultado = $localController->registrar(
                    $idComerciante,
                    $nombreTipoLocal,
                    $nombreLocal,
                    $telefono,
                    $correo,
                    $descripcion,
                    $productos,
                    $logo,
                    $idProvincia,
                    $idCanton,
                    $idDistrito,
                    $direccion,
                    $referencia
                );

                echo $resultado ? "\nLocal registrado con ID $resultado\n" : "\nError al registrar local\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "5":

            $locales = $localController->listar();

            echo "\n------ LOCALES ------\n";
            foreach ($locales as $local) {
                echo "ID: " . $local->getIdLocal() . "\n";
                echo "Nombre: " . $local->getNombreLocal() . "\n";
                echo "Correo: " . $local->getCorreo() . "\n";
                echo "Teléfono: " . $local->getTelefono() . "\n";
                echo "--------------------\n";
            }

            break;

        case "6":

            $id = (int) leer("Ingrese ID del local: ");
            $resultado = $localController->buscarConUbicacion($id);

            if ($resultado != null) {
                $local = $resultado["local"];
                $ubicacion = $resultado["ubicacion"];

                echo "\nLOCAL\n";
                echo $local->getNombreLocal() . "\n";
                echo "Ubicación (IDs): " . $ubicacion->getIdProvincia() . ", "
                    . $ubicacion->getIdCanton() . ", "
                    . $ubicacion->getIdDistrito() . "\n";
                echo "Dirección: " . $ubicacion->getDireccionExacta() . "\n";
            } else {
                echo "No existe ese local\n";
            }

            break;

        case "7":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $idTipoLocalInput = leerOpcional("Filtrar por ID de tipo de local (Enter para omitir): ");
            $idTipoLocal = $idTipoLocalInput === null ? null : (int) $idTipoLocalInput;

            $filtrarUbicacion = leer("¿Filtrar también por ubicación? (s/n): ");

            $idProvincia = null;
            $idCanton = null;
            $idDistrito = null;

            if (strtolower($filtrarUbicacion) === "s") {
                [$idProvincia, $idCanton, $idDistrito] = elegirUbicacionEnCascada(
                    $provinciaController,
                    $cantonController,
                    $distritoController
                );
            }

            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $localController->buscarConFiltros(
                $nombre,
                $idTipoLocal,
                $idProvincia,
                $idCanton,
                $idDistrito,
                $activo
            );

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $local) {
                echo "ID: " . $local->getIdLocal() . "\n";
                echo "Nombre: " . $local->getNombreLocal() . "\n";
                echo "Activo: " . ($local->isActivo() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "8":

            $id = (int) leer("ID del local a eliminar: ");
            echo $localController->eliminar($id) ? "Local eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "9":

            $idLocal = (int) leer("\nID del local: ");
            $idTipoProducto = (int) leer("ID del tipo de producto: ");
            $nombre = leer("Nombre del producto: ");
            $precio = (float) leer("Precio: ");
            $descuentoInput = leerOpcional("Porcentaje de descuento (opcional, Enter para omitir): ");
            $descuento = $descuentoInput === null ? null : (float) $descuentoInput;
            $descripcion = leerOpcional("Descripción (opcional, Enter para omitir): ");
            $cantidad = (int) leer("Cantidad disponible: ");
            $imagen = leerOpcional("Imagen (opcional, Enter para omitir): ");

            try {
                $resultado = $productoController->registrar(
                    $idLocal,
                    $idTipoProducto,
                    $nombre,
                    $precio,
                    $descuento,
                    $descripcion,
                    $cantidad,
                    $imagen
                );

                echo $resultado ? "\nProducto registrado con ID $resultado\n" : "\nError al registrar producto\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "10":

            $productos = $productoController->listar();

            echo "\n------ PRODUCTOS ------\n";
            foreach ($productos as $producto) {
                echo "ID: " . $producto->getIdProducto() . "\n";
                echo "Nombre: " . $producto->getNombre() . "\n";
                echo "Precio original: " . $producto->getPrecioOriginal() . "\n";
                echo "Precio final: " . $producto->getPrecioFinal() . "\n";
                echo "Agotado: " . ($producto->isAgotado() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "11":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $idLocalInput = leerOpcional("Filtrar por ID de local (Enter para omitir): ");
            $idLocal = $idLocalInput === null ? null : (int) $idLocalInput;
            $precioMinInput = leerOpcional("Precio mínimo (Enter para omitir): ");
            $precioMin = $precioMinInput === null ? null : (float) $precioMinInput;
            $precioMaxInput = leerOpcional("Precio máximo (Enter para omitir): ");
            $precioMax = $precioMaxInput === null ? null : (float) $precioMaxInput;
            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $productoController->buscarConFiltros(
                $nombre,
                $idLocal,
                null,
                $precioMin,
                $precioMax,
                $activo
            );

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $producto) {
                echo "ID: " . $producto->getIdProducto() . "\n";
                echo "Nombre: " . $producto->getNombre() . "\n";
                echo "Precio final: " . $producto->getPrecioFinal() . "\n";
                echo "--------------------\n";
            }

            break;

        case "12":

            $id = (int) leer("ID del producto a eliminar: ");
            echo $productoController->eliminar($id) ? "Producto eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "0":
            echo "\nSaliendo...\n";
            break;

        default:
            echo "\nOpción inválida\n";
    }

} while ($opcion !== "0");