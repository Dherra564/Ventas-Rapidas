<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function tablaExisteHistorialCoordenadas(PDO $conexion, string $tabla): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = :tabla";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla]);
    return (int) $consulta->fetchColumn() > 0;
}

$tabla = "tbubicacioncoordenadashistorico";

try {
    if (tablaExisteHistorialCoordenadas($conexion, $tabla)) {
        echo "La tabla $tabla ya existe. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            CREATE TABLE tbubicacioncoordenadashistorico (
                tbubicacioncoordenadashistoricoid INT PRIMARY KEY,
                tbubicacionid INT,
                idusuario INT,
                tipousuario VARCHAR(20),
                latitudanterior DECIMAL(10,7),
                longitudanterior DECIMAL(10,7),
                latitudnueva DECIMAL(10,7),
                longitudnueva DECIMAL(10,7),
                fecha DATETIME,
                activo TINYINT(1)
            )
        ");
        echo "Listo: se creó la tabla $tabla.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}