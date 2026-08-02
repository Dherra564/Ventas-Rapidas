<?php

require_once __DIR__ . "/Configuracion.php";


class BaseDatos
{
    private static ?PDO $conexion = null;

    public static function obtenerConexion(): PDO
    {

        if (self::$conexion === null) {

            try {

                $dsn = "mysql:host=" . Configuracion::SERVIDOR .
                    ";port=" . Configuracion::PUERTO .
                    ";dbname=" . Configuracion::BASE_DATOS .
                    ";charset=" . Configuracion::CHARSET;

                self::$conexion = new PDO(
                    $dsn,
                    Configuracion::USUARIO,
                    Configuracion::getPassword()
                );

                self::$conexion->setAttribute(
                    PDO::ATTR_ERRMODE,
                    PDO::ERRMODE_EXCEPTION
                );

                self::$conexion->setAttribute(
                    PDO::ATTR_DEFAULT_FETCH_MODE,
                    PDO::FETCH_ASSOC
                );

            } catch (PDOException $e) {

                die(
                    "Error de conexión a la base de datos: "
                    . $e->getMessage()
                );

            }

        }

        return self::$conexion;

    }

}