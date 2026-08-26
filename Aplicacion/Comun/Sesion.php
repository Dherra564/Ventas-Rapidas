<?php

class Sesion
{
    public const TIPO_CLIENTE = "Cliente";
    public const TIPO_COMERCIANTE = "Comerciante";

    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function iniciarSesionUsuario(int $id, string $tipo, string $nombre): void
    {
        self::iniciar();
        session_regenerate_id(true);

        $_SESSION["usuarioId"] = $id;
        $_SESSION["usuarioTipo"] = $tipo;
        $_SESSION["usuarioNombre"] = $nombre;
    }

    public static function usuarioActual(): ?array
    {
        self::iniciar();

        if (!isset($_SESSION["usuarioId"])) {
            return null;
        }

        return [
            "id" => (int) $_SESSION["usuarioId"],
            "tipo" => $_SESSION["usuarioTipo"],
            "nombre" => $_SESSION["usuarioNombre"]
        ];
    }

    public static function cerrar(): void
    {
        self::iniciar();
        $_SESSION = [];
        session_destroy();
    }

    /**
     * Corta la ejecución con 401/403 si no hay sesión válida.
     * Úsalo al inicio de cualquier endpoint que requiera estar logueado.
     */
    public static function requerirSesion(?string $tipoRequerido = null): array
    {
        $usuario = self::usuarioActual();

        if ($usuario === null) {
            http_response_code(401);
            echo json_encode(["exito" => false, "mensaje" => "No hay una sesión activa"]);
            exit;
        }

        if ($tipoRequerido !== null && $usuario["tipo"] !== $tipoRequerido) {
            http_response_code(403);
            echo json_encode(["exito" => false, "mensaje" => "No tienes permiso para esta acción"]);
            exit;
        }

        return $usuario;
    }
}