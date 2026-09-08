<?php

class Configuracion
{
    public const SERVIDOR = "localhost";
    public const PUERTO = "3306";
    public const BASE_DATOS = "bdrapiventa";
    public const USUARIO = "root";
    public const CHARSET = "utf8mb4";

    public static function getPassword()
    {
        return ""; // vacío por defecto en XAMPP
    }
}