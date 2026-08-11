<?php

$rutaArchivo = __DIR__ . "/Base-Datos/DatosIniciales/ubicaciones.txt";

function obtenerJson(string $url): array
{
    $contenido = file_get_contents($url);
    if ($contenido === false) {
        throw new Exception("No se pudo consultar: $url");
    }
    return json_decode($contenido, true);
}

$archivo = fopen($rutaArchivo, "w");

echo "Descargando provincias...\n";
$provincias = obtenerJson("https://ubicaciones.paginasweb.cr/provincias.json");

foreach ($provincias as $apiIdProvincia => $nombreProvincia) {
    fwrite($archivo, "P|$nombreProvincia\n");
    echo "  Provincia: $nombreProvincia\n";

    $cantones = obtenerJson("https://ubicaciones.paginasweb.cr/provincia/{$apiIdProvincia}/cantones.json");

    foreach ($cantones as $apiIdCanton => $nombreCanton) {
        fwrite($archivo, "C|$nombreCanton\n");
        echo "    Cantón: $nombreCanton\n";

        $distritos = obtenerJson("https://ubicaciones.paginasweb.cr/provincia/{$apiIdProvincia}/canton/{$apiIdCanton}/distritos.json");

        foreach ($distritos as $nombreDistrito) {
            fwrite($archivo, "D|$nombreDistrito\n");
        }
        echo "      " . count($distritos) . " distritos\n";
    }
}

fclose($archivo);
echo "\n¡Listo! Archivo generado en: $rutaArchivo\n";
