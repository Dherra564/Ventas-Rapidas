<?php

trait GeneradorId
{
    protected function generarSiguienteId(PDO $conexion, string $tabla, string $columnaId): int
    {
        $sql = "SELECT COALESCE(MAX($columnaId), 0) + 1 AS siguiente FROM $tabla";
        $resultado = $conexion->query($sql)->fetch(PDO::FETCH_ASSOC);
        return (int) $resultado["siguiente"];
    }
}