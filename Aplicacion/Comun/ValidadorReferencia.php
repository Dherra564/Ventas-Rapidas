<?php

trait ValidadorReferencia
{
    protected function validarReferencia(
        PDO $conexion,
        string $tabla,
        string $columnaId,
        int $id,
        string $mensajeError
    ): void {
        $sql = "SELECT COUNT(*) AS total FROM $tabla WHERE $columnaId = :id";

        $consulta = $conexion->prepare($sql);
        $consulta->execute([":id" => $id]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if ((int) $fila["total"] === 0) {
            throw new InvalidArgumentException($mensajeError);
        }
    }
}