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

$tablasHuerfanas = [
    "tbclientecorreohistorico",
    "tbclientenombrecompletohistorico",
    "tbclientepasswordhistorico",
    "tbclienteperfilimagenhistorico",
    "tbcomerciantecorreohistorico",
    "tbcomerciantenombrehistorico",
    "tbcomerciantepasswordhistorico",
    "tbcomercianteperfilimagenhistorico",
];

try {
    foreach ($tablasHuerfanas as $tabla) {
        if (!tablaExiste($conexion, $tabla)) {
            echo "La tabla {$tabla} ya no existe. No se hizo ningún cambio ahí.\n";
            continue;
        }

        $conexion->exec("DROP TABLE {$tabla}");
        echo "Listo: se eliminó la tabla {$tabla}.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}