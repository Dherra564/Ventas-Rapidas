<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function tablaExiste(PDO $conexion, string $tabla): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE()
              AND table_name = :tabla";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla]);
    return (int) $consulta->fetchColumn() > 0;
}

try {
    if (tablaExiste($conexion, "tbcarritocompra")) {
        echo "La tabla tbcarritocompra ya existe. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            CREATE TABLE tbcarritocompra (
                tbcarritocompraid INT NOT NULL,
                tbclienteid INT DEFAULT NULL,
                tblocalid INT DEFAULT NULL,
                tbcarritocompraregistrofecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                tbcarritocompraactualizacionfecha DATETIME DEFAULT NULL,
                tbcarritocompraactivo TINYINT(4) DEFAULT 1,
                PRIMARY KEY (tbcarritocompraid)
            )
        ");
        echo "Listo: se creó la tabla tbcarritocompra.\n";
    }

    if (tablaExiste($conexion, "tbcarritocompradetalle")) {
        echo "La tabla tbcarritocompradetalle ya existe. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            CREATE TABLE tbcarritocompradetalle (
                tbcarritocompradetalleid INT NOT NULL,
                tbcarritocompraid INT DEFAULT NULL,
                tbproductoid INT DEFAULT NULL,
                tbcarritocompradetallecantidad INT DEFAULT NULL,
                tbcarritocompradetalleregistrofecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                tbcarritocompradetalleactivo TINYINT(4) DEFAULT 1,
                PRIMARY KEY (tbcarritocompradetalleid)
            )
        ");
        echo "Listo: se creó la tabla tbcarritocompradetalle.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}