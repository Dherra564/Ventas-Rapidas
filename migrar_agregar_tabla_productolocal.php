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
    if (tablaExiste($conexion, "tbproductolocal")) {
        echo "La tabla tbproductolocal ya existe. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            CREATE TABLE tbproductolocal (
                tbproductolocalid INT NOT NULL,
                tbproductoid INT NOT NULL,
                tblocalid INT NOT NULL,
                tbproductolocalactivo TINYINT(1) DEFAULT 1,
                PRIMARY KEY (tbproductolocalid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");
        echo "Listo: se creó la tabla tbproductolocal.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}
