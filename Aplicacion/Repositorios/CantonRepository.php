<?php

require_once __DIR__ . "/../Modelos/Canton.php";
require_once __DIR__ . "/../Comun/LectorUbicaciones.php";

/**
 * Ya no usa la base de datos: los cantones salen de
 * Base-Datos/DatosIniciales/ubicaciones.txt a través de LectorUbicaciones.
 */
class CantonRepository
{
    public function obtenerTodos(): array
    {
        $todos = [];

        foreach (LectorUbicaciones::provincias() as $provincia) {
            foreach (LectorUbicaciones::cantonesPorProvincia($provincia["idProvincia"]) as $c) {
                $todos[] = new Canton($provincia["idProvincia"], $c["nombre"], true, $c["idCanton"]);
            }
        }

        return $todos;
    }

    public function obtenerPorId(int $idCanton): ?Canton
    {
        $c = LectorUbicaciones::cantonPorId($idCanton);

        if ($c === null) {
            return null;
        }

        return new Canton($c["idProvincia"], $c["nombre"], true, $c["idCanton"]);
    }

    public function obtenerPorProvincia(int $idProvincia): array
    {
        return array_map(
            fn($c) => new Canton($idProvincia, $c["nombre"], true, $c["idCanton"]),
            LectorUbicaciones::cantonesPorProvincia($idProvincia)
        );
    }
}