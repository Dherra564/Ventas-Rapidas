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
    if (!columnaExiste($conexion, "tbcliente", "tbclientecorreo")) {
        echo "Las columnas duplicadas de tbcliente ya se eliminaron. No se hizo ningún cambio ahí.\n";
    } else {
        $conexion->exec("
            ALTER TABLE tbcliente
                DROP COLUMN tbclienteidentificacionnumero,
                DROP COLUMN tbclientenombrecompleto,
                DROP COLUMN tbclienteperfilimagen,
                DROP COLUMN tbclientecorreo,
                DROP COLUMN tbclientepassword
        ");
        echo "Listo: se eliminaron las columnas duplicadas de tbcliente.\n";
    }

    if (!columnaExiste($conexion, "tbcomerciante", "tbcomerciantecorreo")) {
        echo "Las columnas duplicadas de tbcomerciante ya se eliminaron. No se hizo ningún cambio ahí.\n";
    } else {
        $conexion->exec("
            ALTER TABLE tbcomerciante
                DROP COLUMN tbcomercianteidentificacionnumero,
                DROP COLUMN tbcomerciantenombre,
                DROP COLUMN tbcomercianteperfilimagen,
                DROP COLUMN tbcomerciantecorreo,
                DROP COLUMN tbcomerciantepassword
        ");
        echo "Listo: se eliminaron las columnas duplicadas de tbcomerciante.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}
