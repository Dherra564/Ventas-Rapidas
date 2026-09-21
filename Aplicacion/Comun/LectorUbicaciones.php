<?php

class LectorUbicaciones
{
    private static ?array $datos = null;

    private static function rutaArchivo(): string
    {
        return __DIR__ . "/../../Base-Datos/DatosIniciales/ubicaciones.txt";
    }

    private static function cargar(): array
    {
        if (self::$datos !== null) {
            return self::$datos;
        }

        $rutaArchivo = self::rutaArchivo();

        if (!file_exists($rutaArchivo)) {
            throw new RuntimeException("No se encontró el archivo de ubicaciones: $rutaArchivo");
        }

        $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lineas === false) {
            throw new RuntimeException("No se pudo leer el archivo de ubicaciones: $rutaArchivo");
        }

        $provincias = [];
        $cantones = [];
        $distritos = [];

        $idProvincia = 0;
        $idCanton = 0;
        $idDistrito = 0;

        $idProvinciaActual = null;
        $idCantonActual = null;

        foreach ($lineas as $linea) {
            $linea = trim($linea);

            if ($linea === "") {
                continue;
            }

            $partes = explode("|", $linea, 2);

            if (count($partes) !== 2) {
                continue;
            }

            [$tipo, $nombre] = $partes;
            $nombre = trim($nombre);

            if ($tipo === "P") {
                $idProvincia++;
                $idProvinciaActual = $idProvincia;
                $provincias[] = ["idProvincia" => $idProvincia, "nombre" => $nombre];
                continue;
            }

            if ($tipo === "C") {
                $idCanton++;
                $idCantonActual = $idCanton;
                $cantones[] = [
                    "idCanton" => $idCanton,
                    "idProvincia" => $idProvinciaActual,
                    "nombre" => $nombre
                ];
                continue;
            }

            if ($tipo === "D") {
                $idDistrito++;
                $distritos[] = [
                    "idDistrito" => $idDistrito,
                    "idCanton" => $idCantonActual,
                    "nombre" => $nombre
                ];
                continue;
            }
        }

        self::$datos = [
            "provincias" => $provincias,
            "cantones" => $cantones,
            "distritos" => $distritos
        ];

        return self::$datos;
    }

    /** @return array<int, array{idProvincia:int, nombre:string}> */
    public static function provincias(): array
    {
        $lista = self::cargar()["provincias"];
        usort($lista, fn($a, $b) => strcmp($a["nombre"], $b["nombre"]));
        return $lista;
    }

    /** @return array<int, array{idCanton:int, nombre:string}> */
    public static function cantonesPorProvincia(int $idProvincia): array
    {
        $lista = array_values(array_filter(
            self::cargar()["cantones"],
            fn($c) => $c["idProvincia"] === $idProvincia
        ));

        usort($lista, fn($a, $b) => strcmp($a["nombre"], $b["nombre"]));

        return array_map(fn($c) => ["idCanton" => $c["idCanton"], "nombre" => $c["nombre"]], $lista);
    }

    /** @return array<int, array{idDistrito:int, nombre:string}> */
    public static function distritosPorCanton(int $idCanton): array
    {
        $lista = array_values(array_filter(
            self::cargar()["distritos"],
            fn($d) => $d["idCanton"] === $idCanton
        ));

        usort($lista, fn($a, $b) => strcmp($a["nombre"], $b["nombre"]));

        return array_map(fn($d) => ["idDistrito" => $d["idDistrito"], "nombre" => $d["nombre"]], $lista);
    }

    /** @return array{idProvincia:int, nombre:string}|null */
    public static function provinciaPorId(int $idProvincia): ?array
    {
        foreach (self::cargar()["provincias"] as $p) {
            if ($p["idProvincia"] === $idProvincia) {
                return $p;
            }
        }
        return null;
    }

    /** @return array{idCanton:int, idProvincia:int, nombre:string}|null */
    public static function cantonPorId(int $idCanton): ?array
    {
        foreach (self::cargar()["cantones"] as $c) {
            if ($c["idCanton"] === $idCanton) {
                return $c;
            }
        }
        return null;
    }

    /** @return array{idDistrito:int, idCanton:int, nombre:string}|null */
    public static function distritoPorId(int $idDistrito): ?array
    {
        foreach (self::cargar()["distritos"] as $d) {
            if ($d["idDistrito"] === $idDistrito) {
                return $d;
            }
        }
        return null;
    }

    public static function existeProvincia(int $idProvincia): bool
    {
        return self::provinciaPorId($idProvincia) !== null;
    }

    public static function existeCanton(int $idCanton): bool
    {
        return self::cantonPorId($idCanton) !== null;
    }

    public static function existeDistrito(int $idDistrito): bool
    {
        return self::distritoPorId($idDistrito) !== null;
    }
}