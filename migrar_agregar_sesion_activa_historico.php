<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function columnaExisteHistorialActividad(PDO $conexion, string $tabla, string $columna): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.columns
            WHERE table_schema = DATABASE() AND table_name = :tabla AND column_name = :columna";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla, ":columna" => $columna]);
    return (int) $consulta->fetchColumn() > 0;
}

try {
    if (columnaExisteHistorialActividad($conexion, "tbsesionactivohistorico", "tblocalid")) {
        echo "La columna tblocalid ya existe en tbsesionactivohistorico. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("ALTER TABLE tbsesionactivohistorico ADD COLUMN tblocalid INT NULL");
        echo "Listo: se agregó la columna tblocalid a tbsesionactivohistorico.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}