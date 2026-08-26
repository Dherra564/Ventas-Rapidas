<?php

require_once __DIR__ . "/Configuracion/BaseDatos.php";

$conexion = BaseDatos::obtenerConexion();

function tablaExiste(PDO $conexion, string $tabla): bool
{
    $sql = "SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = :tabla";
    $consulta = $conexion->prepare($sql);
    $consulta->execute([":tabla" => $tabla]);
    return (int) $consulta->fetchColumn() > 0;
}

try {
    if (tablaExiste($conexion, "tbhistorialactividadsesionlocal")) {
        echo "La tabla tbhistorialactividadsesionlocal ya existe. No se hizo ningún cambio.\n";
    } else {
        $conexion->exec("
            CREATE TABLE tbhistorialactividadsesionlocal (
                tbhistorialactividadsesionlocalid          INT NOT NULL PRIMARY KEY,
                tbhistorialactividadsesionlocalusuarioid   INT NOT NULL,
                tbhistorialactividadsesionlocalusuariotipo VARCHAR(20) NOT NULL,
                tblocalid                                  INT NULL,
                tbhistorialactividadsesionlocaltipo        VARCHAR(50) NOT NULL,
                tbhistorialactividadsesionlocalfecha       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_historialactividadsesion_local FOREIGN KEY (tblocalid) REFERENCES tblocal(tblocalid)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        echo "Listo: se creó la tabla tbhistorialactividadsesionlocal.\n";

        $conexion->exec("
            INSERT INTO tbhistorialactividadsesionlocal
                (tbhistorialactividadsesionlocalid, tbhistorialactividadsesionlocalusuarioid, tbhistorialactividadsesionlocalusuariotipo, tblocalid, tbhistorialactividadsesionlocaltipo, tbhistorialactividadsesionlocalfecha)
            SELECT
                (@fila := @fila + 1) AS id,
                cl.tbcomercianteid,
                'Comerciante',
                cl.tblocalid,
                'EntradaPerfil',
                NOW()
            FROM tbcomerciantelocal cl
            INNER JOIN tblocal l ON l.tblocalid = cl.tblocalid
            JOIN (SELECT @fila := 0) AS inicializador
            WHERE l.tblocalactivo = 1 AND cl.tbcomerciantelocalactivo = 1
        ");
        echo "Se sembró un evento inicial de EntradaPerfil para los locales ya existentes.\n";
    }
} catch (PDOException $e) {
    echo "Error al migrar: " . $e->getMessage() . "\n";
}