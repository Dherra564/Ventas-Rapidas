<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function tablaExisteHistorialPassword(PDO $conexion, string $tabla): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = :tabla";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla]);
    return (int) $consulta->fetchColumn() > 0;
}

$tablas = [
    "tbcomerciantepasswordhistorico" => ["tbcomerciantepasswordhistoricoid", "tbcomercianteid"],
    "tbclientepasswordhistorico" => ["tbclientepasswordhistoricoid", "tbclienteid"],
];

foreach ($tablas as $tabla => [$columnaId, $columnaUsuario]) {
    try {
        if (tablaExisteHistorialPassword($conexion, $tabla)) {
            echo "La tabla $tabla ya existe. No se hizo ningún cambio.\n";
            continue;
        }

        $conexion->exec("
            CREATE TABLE $tabla (
                $columnaId INT PRIMARY KEY,
                $columnaUsuario INT,
                valoranterior VARCHAR(255),
                valornuevo VARCHAR(255),
                fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
                activo TINYINT(1) DEFAULT 1
            )
        ");
        echo "Listo: se creó la tabla $tabla.\n";
    } catch (PDOException $e) {
        echo "Error al migrar $tabla: " . $e->getMessage() . "\n";
    }
}