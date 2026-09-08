<?php

require_once __DIR__ . "/Aplicacion/Controladoras/ComercianteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ClienteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ClienteLocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/LocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProductoController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProductoLocalController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ProvinciaController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/CantonController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/DistritoController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/ReseniaController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/HistorialController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/SuperAdminController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/UbicacionController.php";
require_once __DIR__ . "/Aplicacion/Comun/Sesion.php";

$comercianteController = new ComercianteController();
$clienteController = new ClienteController();
$clienteLocalController = new ClienteLocalController();
$localController = new LocalController();
$productoController = new ProductoController();
$productoLocalController = new ProductoLocalController();
$provinciaController = new ProvinciaController();
$cantonController = new CantonController();
$distritoController = new DistritoController();
$reseniaController = new ReseniaController();
$historialController = new HistorialController();
$superAdminController = new SuperAdminController();
$ubicacionController = new UbicacionController();

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

function leerFloatOpcional(string $etiqueta): ?float
{
    $valor = leerOpcional($etiqueta);
    return $valor === null ? null : (float) $valor;
}

function elegirTipoIdentificacion(): string
{
    echo "\n--- Tipo de identificación ---\n";
    echo "  1. Nacional (cédula)\n";
    echo "  2. Extranjero - DIMEX\n";
    echo "  3. Extranjero - Pasaporte\n";
    $opcion = leer("Seleccione: ");

    return match ($opcion) {
        "2" => "DIMEX",
        "3" => "Pasaporte",
        default => "Cedula",
    };
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

    $sesion = Sesion::usuarioActual();
    $etiquetaSesion = $sesion !== null
        ? "Sesión: {$sesion['tipo']} #{$sesion['id']} ({$sesion['nombre']})"
        : "Sin sesión activa";

    echo "\n=========================================\n";
    echo "   SISTEMA DE PRUEBA — $etiquetaSesion\n";
    echo "=========================================\n";
    echo " 1. Registrar comerciante\n";
    echo " 2. Login comerciante\n";
    echo " 3. Listar comerciantes\n";
    echo " 4. Buscar comerciantes (filtros)\n";
    echo " 5. Cambiar contraseña de comerciante\n";
    echo " 6. Registrar cliente\n";
    echo " 7. Login cliente\n";
    echo " 8. Listar clientes\n";
    echo " 9. Buscar clientes (filtros)\n";
    echo "10. Seguir un local (favorito)\n";
    echo "11. Dejar de seguir un local\n";
    echo "12. Listar locales que sigue un cliente\n";
    echo "13. Registrar local\n";
    echo "14. Listar locales\n";
    echo "15. Buscar local por ID (con ubicación)\n";
    echo "16. Buscar locales (filtros)\n";
    echo "17. Entrar al perfil de un local (marca actividad)\n";
    echo "18. Ver locales de un comerciante\n";
    echo "19. Eliminar local\n";
    echo "20. Registrar producto\n";
    echo "21. Listar productos\n";
    echo "22. Buscar productos (filtros)\n";
    echo "23. Vincular producto existente a otro local\n";
    echo "24. Ver productos de un local (propios + compartidos)\n";
    echo "25. Eliminar producto\n";
    echo "26. Registrar reseña\n";
    echo "27. Ver reseñas y promedio de un local\n";
    echo "28. Ver historial de un campo (nombre/correo/precio/etc.)\n";
    echo "29. Registrar SuperAdmin\n";
    echo "30. Login SuperAdmin\n";
    echo "31. Registrar ubicación GPS del login actual\n";
    echo "32. Cerrar sesión\n";
    echo " 0. Salir\n";
    $opcion = leer("Seleccione una opción: ");

    switch ($opcion) {

        case "1":

            $nombre = leer("\nNombre completo: ");
            $alias = leer("Alias: ");
            $tipoIdentificacion = elegirTipoIdentificacion();
            $numeroIdentificacion = leer("Número de identificación: ");
            $correo = leer("Correo: ");
            $password = leer("Password (mín. 8 caracteres, 1 mayúscula): ");

            try {
                $id = $comercianteController->registrar($nombre, $alias, $tipoIdentificacion, $numeroIdentificacion, $correo, $password);
                echo $id ? "\nComerciante registrado con ID $id\n" : "\nError al registrar comerciante\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "2":

            $correo = leer("\nCorreo: ");
            $password = leer("Password: ");

            try {
                $comerciante = $comercianteController->login($correo, $password);
                Sesion::iniciarSesionUsuario($comerciante->getIdComerciante(), Sesion::TIPO_COMERCIANTE, $comerciante->getNombreCompleto());
                echo "\nSesión iniciada como comerciante #{$comerciante->getIdComerciante()}\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "3":

            foreach ($comercianteController->listar() as $comerciante) {
                echo "ID: " . $comerciante->getIdComerciante() . "\n";
                echo "Nombre: " . $comerciante->getNombreCompleto() . " (" . $comerciante->getAlias() . ")\n";
                echo "Correo: " . $comerciante->getCorreo() . "\n";
                echo "--------------------\n";
            }

            break;

        case "4":

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

        case "5":

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

        case "6":

            $nombreCompleto = leer("\nNombre completo: ");
            $tipoIdentificacion = elegirTipoIdentificacion();
            $numeroIdentificacion = leer("Número de identificación: ");
            $correo = leer("Correo: ");
            $password = leer("Password (mín. 8 caracteres, 1 mayúscula): ");

            $conUbicacion = leer("¿Registrar también su ubicación? (s/n): ");
            $idProvincia = $idCanton = $idDistrito = null;
            $direccionExacta = $referencia = null;

            if (strtolower($conUbicacion) === "s") {
                [$idProvincia, $idCanton, $idDistrito] = elegirUbicacionEnCascada($provinciaController, $cantonController, $distritoController);
                $direccionExacta = leer("Dirección exacta: ");
                $referencia = leerOpcional("Referencia (opcional, Enter para omitir): ");
            }

            try {
                $id = $clienteController->registrar(
                    $nombreCompleto,
                    $tipoIdentificacion,
                    $numeroIdentificacion,
                    $correo,
                    $password,
                    null,
                    $idProvincia,
                    $idCanton,
                    $idDistrito,
                    $direccionExacta,
                    $referencia
                );
                echo $id ? "\nCliente registrado con ID $id\n" : "\nError al registrar cliente\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "7":

            $correo = leer("\nCorreo: ");
            $password = leer("Password: ");

            try {
                $cliente = $clienteController->login($correo, $password);
                Sesion::iniciarSesionUsuario($cliente->getIdCliente(), Sesion::TIPO_CLIENTE, $cliente->getNombreCompleto());
                echo "\nSesión iniciada como cliente #{$cliente->getIdCliente()}\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "8":

            foreach ($clienteController->listar() as $cliente) {
                echo "ID: " . $cliente->getIdCliente() . "\n";
                echo "Nombre: " . $cliente->getNombreCompleto() . "\n";
                echo "Correo: " . $cliente->getCorreo() . "\n";
                echo "--------------------\n";
            }

            break;

        case "9":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $clienteController->buscarConFiltros($nombre, $activo);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $cliente) {
                echo "ID: " . $cliente->getIdCliente() . " - " . $cliente->getNombreCompleto() . "\n";
            }

            break;

        case "10":

            $idCliente = (int) leer("\nID del cliente: ");
            $idLocal = (int) leer("ID del local a seguir: ");

            try {
                $id = $clienteLocalController->seguir($idCliente, $idLocal);
                echo $id ? "\nAhora sigue ese local (ID relación: $id)\n" : "\nError al seguir el local\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "11":

            $idClienteLocal = (int) leer("\nID de la relación cliente-local (ver opción 12): ");
            echo $clienteLocalController->dejarDeSeguir($idClienteLocal) ? "\nDejó de seguir el local\n" : "\nError al quitar\n";

            break;

        case "12":

            $idCliente = (int) leer("\nID del cliente: ");
            $locales = $clienteLocalController->listarPorCliente($idCliente);

            echo "\n------ LOCALES SEGUIDOS (" . count($locales) . ") ------\n";
            foreach ($locales as $fila) {
                echo "ID relación: {$fila['idClienteLocal']} - Local #{$fila['idLocal']}: {$fila['nombreLocal']}\n";
            }

            break;

        case "13":

            $idComerciante = (int) leer("\nID del comerciante dueño: ");

            echo "\n--- Tipo de local (autocompletado) ---\n";
            $textoParcial = leerOpcional("Escriba parte del tipo de local para ver sugerencias (Enter para omitir): ");

            if ($textoParcial !== null) {
                $sugerencias = $localController->buscarTiposCoincidentes($textoParcial);
                if (empty($sugerencias)) {
                    echo "  (Sin coincidencias — se creará como tipo nuevo)\n";
                } else {
                    foreach ($sugerencias as $tipo) {
                        echo "    - {$tipo->getNombre()}\n";
                    }
                }
            }

            $nombreTipoLocal = leer("Escriba el tipo de local definitivo (existente o nuevo): ");
            $nombreLocal = leer("Nombre local: ");
            $telefono = leer("Teléfono (8 dígitos): ");
            $descripcion = leerOpcional("Descripción (opcional, Enter para omitir): ");
            $logo = leerOpcional("Logo (opcional, Enter para omitir): ");

            [$idProvincia, $idCanton, $idDistrito] = elegirUbicacionEnCascada($provinciaController, $cantonController, $distritoController);

            $direccion = leer("Dirección exacta: ");
            $referencia = leerOpcional("Referencia (opcional, Enter para omitir): ");
            $latitud = leerFloatOpcional("Latitud GPS (opcional, Enter para omitir): ");
            $longitud = leerFloatOpcional("Longitud GPS (opcional, Enter para omitir): ");

            try {
                $resultado = $localController->registrar(
                    $idComerciante,
                    $nombreTipoLocal,
                    $nombreLocal,
                    $telefono,
                    $descripcion,
                    $logo,
                    $idProvincia,
                    $idCanton,
                    $idDistrito,
                    $direccion,
                    $referencia,
                    $latitud,
                    $longitud
                );

                echo $resultado ? "\nLocal registrado con ID $resultado\n" : "\nError al registrar local\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "14":

            foreach ($localController->listar() as $local) {
                echo "ID: " . $local->getIdLocal() . "\n";
                echo "Nombre: " . $local->getNombreLocal() . "\n";
                echo "Teléfono: " . $local->getTelefono() . "\n";
                echo "Activo: " . ($local->isActivo() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "15":

            $id = (int) leer("Ingrese ID del local: ");
            $resultado = $localController->buscarConUbicacion($id);

            if ($resultado != null) {
                $local = $resultado["local"];
                $ubicacion = $resultado["ubicacion"];

                echo "\nLOCAL\n";
                echo $local->getNombreLocal() . "\n";
                echo "Ubicación (IDs): " . $ubicacion->getIdProvincia() . ", " . $ubicacion->getIdCanton() . ", " . $ubicacion->getIdDistrito() . "\n";
                echo "Dirección: " . $ubicacion->getDireccionExacta() . "\n";
                if ($ubicacion->tieneCoordenadas()) {
                    echo "GPS: " . $ubicacion->getLatitud() . ", " . $ubicacion->getLongitud() . "\n";
                }
            } else {
                echo "No existe ese local\n";
            }

            break;

        case "16":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $idTipoLocalInput = leerOpcional("Filtrar por ID de tipo de local (Enter para omitir): ");
            $idTipoLocal = $idTipoLocalInput === null ? null : (int) $idTipoLocalInput;

            $filtrarUbicacion = leer("¿Filtrar también por ubicación? (s/n): ");
            $idProvincia = $idCanton = $idDistrito = null;

            if (strtolower($filtrarUbicacion) === "s") {
                [$idProvincia, $idCanton, $idDistrito] = elegirUbicacionEnCascada($provinciaController, $cantonController, $distritoController);
            }

            $activoInput = leerOpcional("Filtrar por activo (1=sí, 0=no, Enter para omitir): ");
            $activo = $activoInput === null ? null : (bool) (int) $activoInput;

            $resultados = $localController->buscarConFiltros($nombre, $idTipoLocal, $idProvincia, $idCanton, $idDistrito, $activo);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $local) {
                echo "ID: " . $local->getIdLocal() . " - " . $local->getNombreLocal() . " (activo: " . ($local->isActivo() ? "sí" : "no") . ")\n";
            }

            break;

        case "17":

            $idLocal = (int) leer("\nID del local: ");
            $idComerciante = (int) leer("ID del comerciante que entra: ");

            try {
                $localController->entrarPerfil($idLocal, $idComerciante);
                echo "\nActividad registrada — el local queda marcado como activo\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "18":

            $idComerciante = (int) leer("\nID del comerciante: ");
            $locales = $localController->listarPorComerciante($idComerciante);

            echo "\n------ LOCALES DEL COMERCIANTE (" . count($locales) . ") ------\n";
            foreach ($locales as $local) {
                echo "ID: " . $local->getIdLocal() . " - " . $local->getNombreLocal() . " (activo: " . ($local->isActivo() ? "sí" : "no") . ")\n";
            }

            break;

        case "19":

            $id = (int) leer("ID del local a eliminar: ");
            echo $localController->eliminar($id) ? "Local eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "20":

            $idLocal = (int) leer("\nID del local: ");

            echo "\n--- Tipo de producto (autocompletado) ---\n";
            $textoParcial = leerOpcional("Escriba parte del tipo de producto para ver sugerencias (Enter para omitir): ");

            if ($textoParcial !== null) {
                $sugerencias = $productoController->buscarTiposCoincidentes($textoParcial);
                foreach ($sugerencias as $tipo) {
                    echo "    - {$tipo->getNombre()}\n";
                }
            }

            $nombreTipoProducto = leer("Escriba el tipo de producto definitivo (existente o nuevo): ");
            $nombre = leer("Nombre del producto: ");
            $precio = (float) leer("Precio: ");
            $descuentoInput = leerOpcional("Porcentaje de descuento (opcional, Enter para omitir): ");
            $descuento = $descuentoInput === null ? null : (float) $descuentoInput;
            $descripcion = leerOpcional("Descripción (opcional, Enter para omitir): ");
            $cantidad = (int) leer("Cantidad disponible: ");
            $imagen = leerOpcional("Imagen (opcional, Enter para omitir): ");

            try {
                $resultado = $productoController->registrar($idLocal, $nombreTipoProducto, $nombre, $precio, $descuento, $descripcion, $cantidad, $imagen);
                echo $resultado ? "\nProducto registrado con ID $resultado\n" : "\nError al registrar producto\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "21":

            foreach ($productoController->listar() as $producto) {
                echo "ID: " . $producto->getIdProducto() . " - " . $producto->getNombre();
                echo " - Precio final: " . $producto->getPrecioFinal();
                echo " - Agotado: " . ($producto->isAgotado() ? "Sí" : "No") . "\n";
            }

            break;

        case "22":

            $nombre = leerOpcional("\nFiltrar por nombre (Enter para omitir): ");
            $idLocalInput = leerOpcional("Filtrar por ID de local (Enter para omitir): ");
            $idLocal = $idLocalInput === null ? null : (int) $idLocalInput;
            $precioMin = leerFloatOpcional("Precio mínimo (Enter para omitir): ");
            $precioMax = leerFloatOpcional("Precio máximo (Enter para omitir): ");

            $resultados = $productoController->buscarConFiltros($nombre, $idLocal, null, $precioMin, $precioMax, null);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";
            foreach ($resultados as $producto) {
                echo "ID: " . $producto->getIdProducto() . " - " . $producto->getNombre() . " - " . $producto->getPrecioFinal() . "\n";
            }

            break;

        case "23":

            $idProducto = (int) leer("\nID del producto existente: ");
            $idLocal = (int) leer("ID del local donde también se venderá: ");

            try {
                $id = $productoLocalController->agregar($idProducto, $idLocal);
                echo $id ? "\nProducto vinculado a ese local (ID relación: $id)\n" : "\nError al vincular\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "24":

            $idLocal = (int) leer("\nID del local: ");
            $productos = $productoController->listarPorLocal($idLocal);

            echo "\n------ PRODUCTOS DEL LOCAL (" . count($productos) . ") ------\n";
            foreach ($productos as $producto) {
                echo "ID: " . $producto->getIdProducto() . " - " . $producto->getNombre() . " - " . $producto->getPrecioFinal() . "\n";
            }

            break;

        case "25":

            $id = (int) leer("ID del producto a eliminar: ");
            echo $productoController->eliminar($id) ? "Producto eliminado correctamente\n" : "Error al eliminar\n";

            break;

        case "26":

            $idCliente = (int) leer("\nID del cliente: ");
            $idLocal = (int) leer("ID del local: ");
            $comentario = leer("Comentario: ");
            $puntuacion = (int) leer("Puntuación (1-5): ");

            try {
                $id = $reseniaController->registrar($idCliente, $idLocal, $comentario, $puntuacion);
                echo $id ? "\nReseña registrada con ID $id\n" : "\nError al registrar reseña\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "27":

            $idLocal = (int) leer("\nID del local: ");
            $reseñas = $reseniaController->listarPorLocal($idLocal);
            $promedio = $reseniaController->promedioPorLocal($idLocal);

            echo "\n------ RESEÑAS (" . count($reseñas) . ") — Promedio: " . ($promedio ?? "sin reseñas") . " ------\n";
            foreach ($reseñas as $resenia) {
                echo "#{$resenia->getIdResenia()} - {$resenia->getPuntuacion()}★ - {$resenia->getComentario()}\n";
            }

            break;

        case "28":

            echo "\nEntidades disponibles: Comerciante, Cliente, Local, Ubicacion, Producto\n";
            $entidad = leer("Entidad: ");
            $campo = leer("Campo (ej. nombre, correo, telefono, precio, descuento, provincia...): ");
            $idEntidad = (int) leer("ID de la entidad: ");

            try {
                $historial = $historialController->listarHistorial($entidad, $campo, $idEntidad);
                echo "\n------ HISTORIAL (" . count($historial) . ") ------\n";
                foreach ($historial as $registro) {
                    $fecha = $registro->getFecha()?->format("Y-m-d H:i:s") ?? "(sin fecha)";
                    echo "[$fecha] {$registro->getValorAnterior()} → {$registro->getValorNuevo()}\n";
                }
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "29":

            $correo = leer("\nCorreo: ");
            $nombreCompleto = leer("Nombre completo: ");
            $password = leer("Password (mín. 8 caracteres, 1 mayúscula): ");

            try {
                $id = $superAdminController->registrar($correo, $nombreCompleto, $password);
                echo $id ? "\nSuperAdmin registrado con ID $id\n" : "\nError al registrar\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "30":

            $correo = leer("\nCorreo: ");
            $password = leer("Password: ");

            try {
                $superAdmin = $superAdminController->login($correo, $password);
                Sesion::iniciarSesionUsuario($superAdmin->getIdSuperAdmin(), Sesion::TIPO_SUPERADMIN, $superAdmin->getNombreCompleto());
                echo "\nSesión iniciada como SuperAdmin #{$superAdmin->getIdSuperAdmin()}\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "31":

            if ($sesion === null) {
                echo "\nPrimero inicia sesión (opciones 2, 7 o 30)\n";
                break;
            }

            $latitud = (float) leer("\nLatitud: ");
            $longitud = (float) leer("Longitud: ");

            try {
                $ubicacionController->registrarUbicacionLogin($sesion["id"], $sesion["tipo"], $latitud, $longitud);
                echo "\nUbicación GPS registrada para la sesión actual\n";
            } catch (Exception $e) {
                echo "\nError: " . $e->getMessage() . "\n";
            }

            break;

        case "32":

            Sesion::cerrar();
            echo "\nSesión cerrada\n";

            break;

        case "0":
            echo "\nSaliendo...\n";
            break;

        default:
            echo "\nOpción inválida\n";
    }

} while ($opcion !== "0");