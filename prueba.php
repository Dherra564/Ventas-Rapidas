<?php

require_once __DIR__ . "/Aplicacion/Controladoras/ComercianteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ClienteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ClienteLocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/LocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProductoController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProvinciaController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/CantonController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/DistritoController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/RegistroCompraController.php";

$comercianteController = new ComercianteController();
$clienteController = new ClienteController();
$clienteLocalController = new ClienteLocalController();
$localController = new LocalController();
$productoController = new ProductoController();
$provinciaController = new ProvinciaController();
$cantonController = new CantonController();
$distritoController = new DistritoController();
$registroCompraController = new RegistroCompraController();

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

function elegirTipoIdentificacion(): string
{
    echo "\n--- Tipo de identificación ---\n";
    echo "  1. Nacional (cédula)\n";
    echo "  2. Extranjero - DIMEX\n";
    echo "  3. Extranjero - Pasaporte\n";
    $opcion = leer("Seleccione: ");

    return match ($opcion) {
        "1" => "Cedula",
        "2" => "DIMEX",
        "3" => "Pasaporte",
        default => "Cedula",
    };
}

do {

    echo "\n=========================\n";
    echo "   SISTEMA DE PRUEBA\n";
    echo "=========================\n";
    echo " 1. Registrar comerciante\n";
    echo " 2. Listar comerciantes\n";
    echo " 3. Buscar comerciantes (filtros)\n";
    echo " 4. Cambiar contraseña de comerciante\n";
    echo " 5. Registrar cliente\n";
    echo " 6. Listar clientes\n";
    echo " 7. Buscar clientes (filtros)\n";
    echo " 8. Cambiar contraseña de cliente\n";
    echo " 9. Agregar local a favoritos\n";
    echo "10. Quitar local de favoritos\n";
    echo "11. Listar favoritos de un cliente\n";
    echo "12. Registrar local\n";
    echo "13. Listar locales\n";
    echo "14. Buscar local por ID (con ubicación)\n";
    echo "15. Buscar locales (filtros)\n";
    echo "16. Eliminar local\n";
    echo "17. Registrar producto\n";
    echo "18. Listar productos\n";
    echo "19. Buscar productos (filtros)\n";
    echo "20. Eliminar producto\n";
    echo "21. Registrar compra\n";
    echo "22. Ver compras de un cliente\n";
    echo "23. Ver compras de un local en una fecha\n";
    echo "24. Ver locales más comprados\n";
    echo " 0. Salir\n";
    $opcion = leer("Seleccione una opción: ");

    switch ($opcion) {

        case "1":

            $nombreCompleto = leer("\nNombre completo: ");
            $alias = leer("Alias: ");
            $tipoIdentificacion = elegirTipoIdentificacion();
            $numeroIdentificacion = leer("Número de identificación: ");
            $correo = leer("Correo: ");
            $password = leer("Password (mín. 8 caracteres, 1 mayúscula): ");

            try {
                $id = $comercianteController->registrar(
                    $nombreCompleto,
                    $alias,
                    $tipoIdentificacion,
                    $numeroIdentificacion,
                    $correo,
                    $password
                );
                echo $id ? "\nComerciante registrado con ID $id\n" : "\nError al registrar comerciante\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "2":

            foreach ($comercianteController->listar() as $comerciante) {
                echo "ID: " . $comerciante->getIdComerciante() . "\n";
                echo "Nombre: " . $comerciante->getNombreCompleto() . " (" . $comerciante->getAlias() . ")\n";
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
                echo "ID: " . $comerciante->getIdComerciante() . " - " . $comerciante->getNombreCompleto() . "\n";
            }

            break;

        case "4":

            $idComerciante = (int) leer("\nID del comerciante: ");
            $passwordActual = leer("Contraseña actual: ");
            $passwordNueva = leer("Contraseña nueva: ");

            try {
                $exito = $comercianteController->cambiarPassword($idComerciante, $passwordActual, $passwordNueva);
                echo $exito ? "\nContraseña actualizada\n" : "\nNo se pudo actualizar\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "5":

            $nombreCompleto = leer("\nNombre completo: ");
            $tipoIdentificacion = elegirTipoIdentificacion();
            $numeroIdentificacion = leer("Número de identificación: ");
            $correo = leer("Correo: ");
            $password = leer("Password (mín. 8 caracteres, 1 mayúscula): ");

            try {
                $id = $clienteController->registrar(
                    $nombreCompleto,
                    $tipoIdentificacion,
                    $numeroIdentificacion,
                    $correo,
                    $password
                );
                echo $id ? "\nCliente registrado con ID $id\n" : "\nError al registrar cliente\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "6":

            foreach ($clienteController->listar() as $cliente) {
                echo "ID: " . $cliente->getIdCliente() . "\n";
                echo "Nombre: " . $cliente->getNombreCompleto() . "\n";
                echo "Correo: " . $cliente->getCorreo() . "\n";
                echo "--------------------\n";
            }

            break;

        case "7":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $clienteController->buscarConFiltros($nombre, $activo);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $cliente) {
                echo "ID: " . $cliente->getIdCliente() . " - " . $cliente->getNombreCompleto() . "\n";
            }

            break;

        case "8":

            $idCliente = (int) leer("\nID del cliente: ");
            $passwordActual = leer("Contraseña actual: ");
            $passwordNueva = leer("Contraseña nueva: ");

            try {
                $exito = $clienteController->cambiarPassword($idCliente, $passwordActual, $passwordNueva);
                echo $exito ? "\nContraseña actualizada\n" : "\nNo se pudo actualizar\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "9":

            $idCliente = (int) leer("\nID del cliente: ");
            $idLocal = (int) leer("ID del local a agregar a favoritos: ");

            try {
                $id = $clienteLocalController->agregarFavorito($idCliente, $idLocal);
                echo $id ? "\nAgregado a favoritos\n" : "\nError al agregar\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "10":

            $idCliente = (int) leer("\nID del cliente: ");
            $idLocal = (int) leer("ID del local a quitar de favoritos: ");

            echo $clienteLocalController->quitarFavorito($idCliente, $idLocal)
                ? "\nQuitado de favoritos\n"
                : "\nError al quitar\n";

            break;

        case "11":

            $idCliente = (int) leer("\nID del cliente: ");
            $idsLocales = $clienteLocalController->listarFavoritos($idCliente);

            echo "\n------ LOCALES FAVORITOS ------\n";
            if (empty($idsLocales)) {
                echo "(Sin favoritos)\n";
            }
            foreach ($idsLocales as $idLocal) {
                $local = $localController->buscar($idLocal);
                echo "ID: $idLocal - " . ($local ? $local->getNombreLocal() : "(no encontrado)") . "\n";
            }

            break;

        case "12":

            $idComerciante = (int) leer("\nID del comerciante dueño: ");

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
            $telefono = leer("Teléfono (8 dígitos): ");
            $correo = leer("Correo: ");
            $descripcion = leerOpcional("Descripción (opcional, Enter para omitir): ");
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

        case "13":

            foreach ($localController->listar() as $local) {
                echo "ID: " . $local->getIdLocal() . "\n";
                echo "Nombre: " . $local->getNombreLocal() . "\n";
                echo "Teléfono: " . $local->getTelefono() . "\n";
                echo "--------------------\n";
            }

            break;

        case "14":

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

        case "15":

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
                echo "ID: " . $local->getIdLocal() . " - " . $local->getNombreLocal() . "\n";
            }

            break;

        case "16":

            $id = (int) leer("ID del local a eliminar: ");
            echo $localController->eliminar($id) ? "Local eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "17":

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

        case "18":

            foreach ($productoController->listar() as $producto) {
                echo "ID: " . $producto->getIdProducto() . "\n";
                echo "Nombre: " . $producto->getNombre() . "\n";
                echo "Precio final: " . $producto->getPrecioFinal() . "\n";
                echo "Agotado: " . ($producto->isAgotado() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "19":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $idLocalInput = leerOpcional("Filtrar por ID de local (Enter para omitir): ");
            $idLocal = $idLocalInput === null ? null : (int) $idLocalInput;
            $precioMinInput = leerOpcional("Precio mínimo (Enter para omitir): ");
            $precioMin = $precioMinInput === null ? null : (float) $precioMinInput;
            $precioMaxInput = leerOpcional("Precio máximo (Enter para omitir): ");
            $precioMax = $precioMaxInput === null ? null : (float) $precioMaxInput;

            $resultados = $productoController->buscarConFiltros($nombre, $idLocal, null, $precioMin, $precioMax, null);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $producto) {
                echo "ID: " . $producto->getIdProducto() . " - " . $producto->getNombre() . " - " . $producto->getPrecioFinal() . "\n";
            }

            break;

        case "20":

            $id = (int) leer("ID del producto a eliminar: ");
            echo $productoController->eliminar($id) ? "Producto eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "21":

            $idCliente = (int) leer("\nID del cliente que compró: ");
            $idLocal = (int) leer("ID del local donde compró: ");

            try {
                $id = $registroCompraController->registrar($idCliente, $idLocal);
                echo $id ? "\nCompra registrada con ID $id\n" : "\nError al registrar compra\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "22":

            $idCliente = (int) leer("\nID del cliente: ");
            $compras = $registroCompraController->listarPorCliente($idCliente);

            echo "\n------ COMPRAS DEL CLIENTE (" . count($compras) . ") ------\n";
            foreach ($compras as $compra) {
                echo "ID compra: " . $compra->getIdRegistroCompra()
                    . " - Local: " . $compra->getIdLocal()
                    . " - Fecha: " . $compra->getFechaCompra()->format("Y-m-d H:i:s") . "\n";
            }

            break;

        case "23":

            $idLocal = (int) leer("\nID del local: ");
            $fecha = leer("Fecha a consultar (formato YYYY-MM-DD): ");

            $compras = $registroCompraController->listarPorLocalYFecha($idLocal, $fecha);

            echo "\n------ COMPRAS DEL LOCAL EN $fecha (" . count($compras) . ") ------\n";
            foreach ($compras as $compra) {
                echo "ID compra: " . $compra->getIdRegistroCompra()
                    . " - Cliente: " . $compra->getIdCliente()
                    . " - Hora: " . $compra->getFechaCompra()->format("H:i:s") . "\n";
            }

            break;

        case "24":

            $limiteInput = leerOpcional("\n¿Cuántos locales mostrar? (Enter = 10): ");
            $limite = $limiteInput === null ? 10 : (int) $limiteInput;

            $ranking = $registroCompraController->localesMasComprados($limite);

            echo "\n------ LOCALES MÁS COMPRADOS ------\n";
            foreach ($ranking as $fila) {
                $local = $localController->buscar((int) $fila["idLocal"]);
                $nombreLocal = $local ? $local->getNombreLocal() : "(local #{$fila['idLocal']})";
                echo "$nombreLocal - {$fila['totalCompras']} compras\n";
            }

            break;

        case "0":
            echo "\nSaliendo...\n";
            break;

        default:
            echo "\nOpción inválida\n";
    }

} while ($opcion !== "0");