<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function columnaExiste(PDO $conexion, string $tabla, string $columna): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.columns
            WHERE table_schema = DATABASE()
              AND table_name = :tabla
              AND column_name = :columna";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla, ":columna" => $columna]);
    return (int) $consulta->fetchColumn() > 0;
}

try {
    if (columnaExiste($conexion, "tbubicacion", "tbubicacionlatitud")) {
        echo "Las columnas de GPS ya existen. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            ALTER TABLE tbubicacion
                ADD COLUMN tbubicacionlatitud DECIMAL(10,7) NULL,
                ADD COLUMN tbubicacionlongitud DECIMAL(10,7) NULL
        ");
        echo "Listo: se agregaron tbubicacionlatitud y tbubicacionlongitud a tbubicacion.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}