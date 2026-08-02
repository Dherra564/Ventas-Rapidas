<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";


try {

    $conexion = BaseDatos::obtenerConexion();

    echo "Conexión exitosa";

} catch (PDOException $e) {
    throw new Exception("Error de conexión a la base de datos: " . $e->getMessage());
}