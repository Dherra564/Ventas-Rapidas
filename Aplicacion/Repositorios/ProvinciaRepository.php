<?php

require_once __DIR__ . "/../Modelos/Provincia.php";
require_once __DIR__ . "/../Comun/LectorUbicaciones.php";

class ProvinciaRepository
{
    public function obtenerTodos(): array
    {
        return array_map(
            fn($p) => new Provincia($p["nombre"], true, $p["idProvincia"]),
            LectorUbicaciones::provincias()
        );
    }

    public function obtenerPorId(int $idProvincia): ?Provincia
    {
        $p = LectorUbicaciones::provinciaPorId($idProvincia);

        if ($p === null) {
            return null;
        }

        return new Provincia($p["nombre"], true, $p["idProvincia"]);
    }
}