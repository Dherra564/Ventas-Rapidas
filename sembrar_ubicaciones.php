<?php

require_once __DIR__ . "/Aplicacion/Repositorios/ProvinciaRepository.php";
require_once __DIR__ . "/Aplicacion/Repositorios/CantonRepository.php";
require_once __DIR__ . "/Aplicacion/Repositorios/DistritoRepository.php";
require_once __DIR__ . "/Aplicacion/Modelos/Provincia.php";
require_once __DIR__ . "/Aplicacion/Modelos/Canton.php";
require_once __DIR__ . "/Aplicacion/Modelos/Distrito.php";

$rutaArchivo = __DIR__ . "/Base-Datos/DatosIniciales/ubicaciones.txt";

if (!file_exists($rutaArchivo)) {
    die("No se encontró el archivo: $rutaArchivo\n");
}

$provinciaRepo = new ProvinciaRepository();
$cantonRepo = new CantonRepository();
$distritoRepo = new DistritoRepository();

$idProvinciaActual = null;
$idCantonActual = null;

$archivo = fopen($rutaArchivo, "r");

while (($linea = fgets($archivo)) !== false) {
    $linea = trim($linea);

    if ($linea === "") {
        continue;
    }

    [$tipo, $nombre] = explode("|", $linea, 2);

    if ($tipo === "P") {
        $idProvinciaActual = $provinciaRepo->insertar(new Provincia($nombre));
        echo "Provincia guardada: $nombre (ID $idProvinciaActual)\n";
        continue;
    }

    if ($tipo === "C") {
        $idCantonActual = $cantonRepo->insertar(new Canton($idProvinciaActual, $nombre));
        echo "  Cantón guardado: $nombre (ID $idCantonActual)\n";
        continue;
    }

    if ($tipo === "D") {
        $distritoRepo->insertar(new Distrito($idCantonActual, $nombre));
        echo "    Distrito guardado: $nombre\n";
        continue;
    }
}

fclose($archivo);

echo "\n¡Listo! Provincias, cantones y distritos cargados desde archivo.\n";