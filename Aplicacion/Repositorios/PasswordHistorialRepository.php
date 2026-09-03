<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/PasswordHistorial.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class PasswordHistorialRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
    }

    public function registrar(PasswordHistorial $historial): int|false
    {
        [$tabla, $columnaId, $columnaUsuario] = $this->resolverTabla($historial->getTipoUsuario());

        $id = $this->generarSiguienteId($this->conexion, $tabla, $columnaId);

        $sql = "INSERT INTO $tabla
                ($columnaId, $columnaUsuario, valoranterior, valornuevo, fecha, activo)
                VALUES (:id, :idUsuario, :valorAnterior, :valorNuevo, NOW(), :activo)";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idUsuario" => $historial->getIdUsuario(),
            ":valorAnterior" => $historial->getPasswordHashAnterior(),
            ":valorNuevo" => $historial->getPasswordHashNuevo(),
            ":activo" => (int) $historial->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerPorUsuario(int $idUsuario, string $tipoUsuario): array
    {
        [$tabla, $columnaId, $columnaUsuario] = $this->resolverTabla($tipoUsuario);

        $sql = "SELECT * FROM $tabla
                WHERE $columnaUsuario = :idUsuario
                ORDER BY fecha DESC, $columnaId DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idUsuario" => $idUsuario]);

        return $this->mapearFilas($consulta, $tipoUsuario, $columnaId, $columnaUsuario);
    }

    // Los últimos $cantidad hashes de contraseña usados (más reciente
    // primero), para poder validar que la nueva no sea igual a ninguno.
    public function obtenerUltimosHashes(int $idUsuario, string $tipoUsuario, int $cantidad = 2): array
    {
        [$tabla, $columnaId, $columnaUsuario] = $this->resolverTabla($tipoUsuario);

        $sql = "SELECT valornuevo FROM $tabla
                WHERE $columnaUsuario = :idUsuario
                ORDER BY fecha DESC, $columnaId DESC
                LIMIT :cantidad";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":idUsuario", $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(":cantidad", $cantidad, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_COLUMN);
    }

    // Cuántas veces cambió la contraseña este usuario.
    public function contarCambios(int $idUsuario, string $tipoUsuario): int
    {
        [$tabla, , $columnaUsuario] = $this->resolverTabla($tipoUsuario);

        $sql = "SELECT COUNT(*) FROM $tabla WHERE $columnaUsuario = :idUsuario";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idUsuario" => $idUsuario]);

        return (int) $consulta->fetchColumn();
    }

    private function resolverTabla(string $tipoUsuario): array
    {
        if ($tipoUsuario === PasswordHistorial::TIPO_COMERCIANTE) {
            return ["tbcomerciantepasswordhistorico", "tbcomerciantepasswordhistoricoid", "tbcomercianteid"];
        }

        if ($tipoUsuario === PasswordHistorial::TIPO_CLIENTE) {
            return ["tbclientepasswordhistorico", "tbclientepasswordhistoricoid", "tbclienteid"];
        }

        throw new InvalidArgumentException("Tipo de usuario inválido: $tipoUsuario");
    }

    private function mapearFilas(PDOStatement $consulta, string $tipoUsuario, string $columnaId, string $columnaUsuario): array
    {
        $registros = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $registros[] = $this->mapearFila($fila, $tipoUsuario, $columnaId, $columnaUsuario);
        }

        return $registros;
    }

    private function mapearFila(array $fila, string $tipoUsuario, string $columnaId, string $columnaUsuario): PasswordHistorial
    {
        return new PasswordHistorial(
            (int) $fila[$columnaUsuario],
            $tipoUsuario,
            $fila["valoranterior"],
            $fila["valornuevo"],
            (bool) $fila["activo"],
            (int) $fila[$columnaId],
            new DateTime($fila["fecha"])
        );
    }
}