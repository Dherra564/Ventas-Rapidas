<?php

class Configuracion
{
    public const SERVIDOR = "mysql-bdrapi-venta-rapida.g.aivencloud.com";
    public const PUERTO = "17239";
    public const BASE_DATOS = "bdrapiventa";
    public const USUARIO = "avnadmin";
    public const CHARSET = "utf8mb4";

    public static function getPassword()
    {
        $parte1 = "AVNS_vWxw9eT3Zf";
        $parte2 = "-SXsxxn35";
        return $parte1 . $parte2;
    }
}