<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function tablaExisteHistorialUbicacion(PDO $conexion, string $tabla): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = :tabla";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla]);
    return (int) $consulta->fetchColumn() > 0;
}

function columnaEsNulableHistorialUbicacion(PDO $conexion, string $tabla, string $columna): bool
{
    $sql = "SELECT IS_NULLABLE FROM information_schema.columns
            WHERE table_schema = DATABASE() AND table_name = :tabla AND column_name = :columna";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla, ":columna" => $columna]);
    return $consulta->fetchColumn() === 'YES';
}

function obtenerNombreLlaveForanea(PDO $conexion, string $tabla, string $columna): ?string
{
    $sql = "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :tabla
              AND COLUMN_NAME = :columna
              AND REFERENCED_TABLE_NAME IS NOT NULL";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla, ":columna" => $columna]);
    $resultado = $consulta->fetchColumn();
    return $resultado !== false ? $resultado : null;
}

$tabla = "tbhistorialubicacion";

try {
    if (!tablaExisteHistorialUbicacion($conexion, $tabla)) {
        $conexion->exec("
            CREATE TABLE tbhistorialubicacion (
                tbhistorialubicacionid            INT NOT NULL PRIMARY KEY,
                tbubicacionid                      INT NULL,
                tbhistorialubicacionusuarioid      INT NOT NULL,
                tbhistorialubicacionusuariotipo    VARCHAR(50) NOT NULL,
                tbhistorialubicacioncampo          VARCHAR(100) NOT NULL,
                tbhistorialubicacionvaloranterior  VARCHAR(500) NULL,
                tbhistorialubicacionvalornuevo     VARCHAR(500) NOT NULL,
                tbhistorialubicacionfecha          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                tbhistorialubicacionactivo         TINYINT NOT NULL DEFAULT 1
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "Listo: se creó la tabla tbhistorialubicacion.\n";
        exit;
    }

    echo "La tabla tbhistorialubicacion ya existe. Revisando si necesita reparación...\n";

    $reparaciones = 0;

    $nombreLlave = obtenerNombreLlaveForanea($conexion, $tabla, "tbubicacionid");
    if ($nombreLlave !== null) {
        $conexion->exec("ALTER TABLE $tabla DROP FOREIGN KEY `$nombreLlave`");
        echo "  + Se eliminó la llave foránea '$nombreLlave' (las validaciones van en código, no en la base de datos).\n";
        $reparaciones++;
    }

    if (!columnaEsNulableHistorialUbicacion($conexion, $tabla, "tbubicacionid")) {
        $conexion->exec("ALTER TABLE $tabla MODIFY COLUMN tbubicacionid INT NULL");
        echo "  + Se permitió NULL en tbubicacionid.\n";
        $reparaciones++;
    }

    if (!columnaEsNulableHistorialUbicacion($conexion, $tabla, "tbhistorialubicacionvaloranterior")) {
        $conexion->exec("ALTER TABLE $tabla MODIFY COLUMN tbhistorialubicacionvaloranterior VARCHAR(500) NULL");
        echo "  + Se permitió NULL en tbhistorialubicacionvaloranterior.\n";
        $reparaciones++;
    }

    echo $reparaciones > 0
        ? "\nListo: se repararon $reparaciones cosa(s) sin perder datos existentes.\n"
        : "\nLa tabla ya estaba correcta. No se hizo ningún cambio.\n";

} catch (PDOException $e) {
    echo "Error al migrar/reparar: " . $e->getMessage() . "\n";
}