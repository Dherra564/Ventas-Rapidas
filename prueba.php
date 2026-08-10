<?php

require_once __DIR__ . "/Aplicacion/Controladoras/ComercianteController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/LocalController.php";

$comercianteController = new ComercianteController();
$localController = new LocalController();

do {

    echo "\n=========================\n";
    echo "   SISTEMA DE PRUEBA\n";
    echo "=========================\n";
    echo "1. Registrar comerciante\n";
    echo "2. Listar comerciantes\n";
    echo "3. Registrar local\n";
    echo "4. Listar locales\n";
    echo "5. Buscar local por ID\n";
    echo "6. Eliminar local\n";
    echo "7. Buscar comerciantes (filtros)\n";
    echo "8. Buscar locales (filtros)\n";
    echo "0. Salir\n";
    echo "Seleccione una opción: ";

    $opcion = trim(fgets(STDIN));

    switch ($opcion) {

        case "1":

            echo "\nNombre completo: ";
            $nombreCompleto = trim(fgets(STDIN));

            echo "Alias: ";
            $alias = trim(fgets(STDIN));

            echo "Cédula: ";
            $cedula = trim(fgets(STDIN));

            echo "Correo: ";
            $correo = trim(fgets(STDIN));

            echo "Password: ";
            $password = trim(fgets(STDIN));

            try {
                $id = $comercianteController->registrar(
                    $nombreCompleto,
                    $alias,
                    $cedula,
                    $correo,
                    $password
                );

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

            echo "\nID del comerciante: ";
            $idComerciante = (int) trim(fgets(STDIN));

            echo "ID del tipo de local: ";
            $idTipoLocal = (int) trim(fgets(STDIN));

            echo "Nombre local: ";
            $nombreLocal = trim(fgets(STDIN));

            echo "Teléfono: ";
            $telefono = trim(fgets(STDIN));

            echo "Correo: ";
            $correo = trim(fgets(STDIN));

            echo "Descripción (opcional, Enter para omitir): ";
            $descripcion = trim(fgets(STDIN));
            $descripcion = $descripcion === "" ? null : $descripcion;

            echo "Productos a ofrecer (opcional, Enter para omitir): ";
            $productos = trim(fgets(STDIN));
            $productos = $productos === "" ? null : $productos;

            echo "Logo (opcional, Enter para omitir): ";
            $logo = trim(fgets(STDIN));
            $logo = $logo === "" ? null : $logo;

            echo "\n--- UBICACIÓN (usar IDs existentes en tbprovincia/tbcanton/tbdistrito) ---\n";

            echo "ID Provincia: ";
            $idProvincia = (int) trim(fgets(STDIN));

            echo "ID Cantón: ";
            $idCanton = (int) trim(fgets(STDIN));

            echo "ID Distrito: ";
            $idDistrito = (int) trim(fgets(STDIN));

            echo "Dirección exacta: ";
            $direccion = trim(fgets(STDIN));

            echo "Referencia (opcional, Enter para omitir): ";
            $referencia = trim(fgets(STDIN));
            $referencia = $referencia === "" ? null : $referencia;

            try {
                $resultado = $localController->registrar(
                    $idComerciante,
                    $idTipoLocal,
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

        case "4":

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

        case "5":

            echo "Ingrese ID del local: ";
            $id = (int) trim(fgets(STDIN));

            $resultado = $localController->buscarConUbicacion($id);

            if ($resultado != null) {

                $local = $resultado["local"];
                $ubicacion = $resultado["ubicacion"];

                echo "\nLOCAL\n";
                echo $local->getNombreLocal() . "\n";

                echo "Ubicación (IDs): ";
                echo $ubicacion->getIdProvincia() . ", "
                    . $ubicacion->getIdCanton() . ", "
                    . $ubicacion->getIdDistrito() . "\n";
                echo "Dirección: " . $ubicacion->getDireccionExacta() . "\n";

            } else {
                echo "No existe ese local\n";
            }

            break;

        case "6":

            echo "ID del local a eliminar: ";
            $id = (int) trim(fgets(STDIN));

            echo $localController->eliminar($id)
                ? "Local eliminado correctamente\n"
                : "Error al eliminar\n";

            break;

        case "7":

            echo "\nFiltrar por nombre (Enter para omitir): ";
            $nombre = trim(fgets(STDIN));
            $nombre = $nombre === "" ? null : $nombre;

            echo "Filtrar por alias (Enter para omitir): ";
            $alias = trim(fgets(STDIN));
            $alias = $alias === "" ? null : $alias;

            echo "Filtrar por activo (1=si, 0=no, Enter para omitir): ";
            $activoInput = trim(fgets(STDIN));
            $activo = $activoInput === "" ? null : (bool) (int) $activoInput;

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

        case "8":

            echo "\nFiltrar por nombre (Enter para omitir): ";
            $nombre = trim(fgets(STDIN));
            $nombre = $nombre === "" ? null : $nombre;

            echo "Filtrar por ID tipo de local (Enter para omitir): ";
            $idTipoLocalInput = trim(fgets(STDIN));
            $idTipoLocal = $idTipoLocalInput === "" ? null : (int) $idTipoLocalInput;

            echo "Filtrar por activo (1=si, 0=no, Enter para omitir): ";
            $activoInput = trim(fgets(STDIN));
            $activo = $activoInput === "" ? null : (bool) (int) $activoInput;

            $resultados = $localController->buscarConFiltros($nombre, $idTipoLocal, $activo);

            echo "\n------ RESULTADOS (" . count($resultados) . ") ------\n";

            foreach ($resultados as $local) {
                echo "ID: " . $local->getIdLocal() . "\n";
                echo "Nombre: " . $local->getNombreLocal() . "\n";
                echo "Activo: " . ($local->isActivo() ? "Sí" : "No") . "\n";
                echo "--------------------\n";
            }

            break;

        case "0":
            echo "\nSaliendo...\n";
            break;

        default:
            echo "\nOpción inválida\n";
    }

} while ($opcion != "0");