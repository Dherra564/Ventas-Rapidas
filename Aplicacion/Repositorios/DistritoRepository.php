<?php

require_once __DIR__ . "/../Modelos/Distrito.php";
require_once __DIR__ . "/../Comun/LectorUbicaciones.php";

class DistritoRepository
{
    public function obtenerTodos(): array
    {
        $todos = [];

        foreach (LectorUbicaciones::provincias() as $provincia) {
            foreach (LectorUbicaciones::cantonesPorProvincia($provincia["idProvincia"]) as $canton) {
                foreach (LectorUbicaciones::distritosPorCanton($canton["idCanton"]) as $d) {
                    $todos[] = new Distrito($canton["idCanton"], $d["nombre"], true, $d["idDistrito"]);
                }
            }
        }

        return $todos;
    }

    public function obtenerPorId(int $idDistrito): ?Distrito
    {
        $d = LectorUbicaciones::distritoPorId($idDistrito);

        if ($d === null) {
            return null;
        }

        return new Distrito($d["idCanton"], $d["nombre"], true, $d["idDistrito"]);
    }

    public function obtenerPorCanton(int $idCanton): array
    {
        return array_map(
            fn($d) => new Distrito($idCanton, $d["nombre"], true, $d["idDistrito"]),
            LectorUbicaciones::distritosPorCanton($idCanton)
        );
    }
}