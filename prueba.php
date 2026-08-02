<?php

require_once __DIR__ . "/Aplicacion/Controladoras/ProveedorController.php";
require_once __DIR__ . "/Aplicacion/Controladoras/LocalController.php";

$proveedorController = new ProveedorController();
$localController = new LocalController();



do {

    echo "\n=========================\n";
    echo "   SISTEMA DE PRUEBA\n";
    echo "=========================\n";
    echo "1. Registrar proveedor\n";
    echo "2. Listar proveedores\n";
    echo "3. Registrar local\n";
    echo "4. Listar locales\n";
    echo "5. Buscar local por ID\n";
    echo "6. Eliminar local\n";
    echo "0. Salir\n";
    echo "Seleccione una opción: ";

    $opcion = trim(fgets(STDIN));



    switch ($opcion) {


        case 1:

            echo "\nNombre: ";
            $nombre = trim(fgets(STDIN));

            echo "Apellido: ";
            $apellido = trim(fgets(STDIN));

            echo "Cédula: ";
            $cedula = trim(fgets(STDIN));

            echo "Correo: ";
            $correo = trim(fgets(STDIN));

            echo "Password: ";
            $password = trim(fgets(STDIN));



            if (
                $proveedorController->registrar(
                    $nombre,
                    $apellido,
                    $cedula,
                    $correo,
                    $password
                )
            ) {

                echo "\nProveedor registrado correctamente\n";

            } else {

                echo "\nError al registrar proveedor\n";
            }


            break;




        case 2:

            $proveedores = $proveedorController->listar();


            echo "\n------ PROVEEDORES ------\n";


            foreach ($proveedores as $proveedor) {


                echo "ID: "
                    . $proveedor->getIdProveedor()
                    . "\n";


                echo "Nombre: "
                    . $proveedor->getNombre()
                    . " "
                    . $proveedor->getApellido()
                    . "\n";


                echo "Correo: "
                    . $proveedor->getCorreo()
                    . "\n";


                echo "--------------------\n";
            }


            break;




        case 3:


            echo "\nID del proveedor: ";
            $idProveedor = (int) trim(fgets(STDIN));


            echo "Nombre local: ";
            $nombreLocal = trim(fgets(STDIN));


            echo "Descripción: ";
            $descripcion = trim(fgets(STDIN));


            echo "Teléfono: ";
            $telefono = trim(fgets(STDIN));


            echo "Correo: ";
            $correo = trim(fgets(STDIN));


            echo "Imagen: ";
            $imagen = trim(fgets(STDIN));



            echo "\n--- UBICACIÓN ---\n";


            echo "Provincia: ";
            $provincia = trim(fgets(STDIN));


            echo "Cantón: ";
            $canton = trim(fgets(STDIN));


            echo "Distrito: ";
            $distrito = trim(fgets(STDIN));


            echo "Dirección exacta: ";
            $direccion = trim(fgets(STDIN));


            echo "Referencia: ";
            $referencia = trim(fgets(STDIN));



            if (
                $localController->registrar(
                    $idProveedor,
                    $nombreLocal,
                    $descripcion,
                    $telefono,
                    $correo,
                    $imagen,
                    $provincia,
                    $canton,
                    $distrito,
                    $direccion,
                    $referencia

                )
            ) {


                echo "\nLocal registrado correctamente\n";


            } else {


                echo "\nError al registrar local\n";

            }


            break;





        case 4:


            $locales = $localController->listar();


            echo "\n------ LOCALES ------\n";


            foreach ($locales as $local) {


                echo "ID: "
                    . $local->getIdLocal()
                    . "\n";


                echo "Nombre: "
                    . $local->getNombreLocal()
                    . "\n";


                echo "Correo: "
                    . $local->getCorreo()
                    . "\n";


                echo "Teléfono: "
                    . $local->getTelefono()
                    . "\n";


                echo "--------------------\n";

            }


            break;




        case 5:


            echo "Ingrese ID del local: ";

            $id = (int) trim(fgets(STDIN));


            $resultado = $localController->buscarConUbicacion($id);



            if ($resultado != null) {


                $local = $resultado["local"];
                $ubicacion = $resultado["ubicacion"];



                echo "\nLOCAL\n";

                echo $local->getNombreLocal()
                    . "\n";


                echo "Ubicación:\n";

                echo $ubicacion->getProvincia()
                    . ", "
                    . $ubicacion->getCanton()
                    . ", "
                    . $ubicacion->getDistrito()
                    . "\n";


            } else {


                echo "No existe ese local\n";

            }


            break;




        case 6:


            echo "ID del local a eliminar: ";

            $id = (int) trim(fgets(STDIN));



            if ($localController->eliminar($id)) {

                echo "Local eliminado correctamente\n";

            } else {

                echo "Error al eliminar\n";

            }


            break;



        case 0:

            echo "\nSaliendo...\n";
            break;



        default:

            echo "\nOpción inválida\n";

    }


} while ($opcion != 0);