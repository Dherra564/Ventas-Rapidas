<?php

require_once __DIR__ . "/Usuario.php";

class Cliente
{
    private int $idCliente;
    private int $idUsuario;
    private bool $activo;
    private ?Usuario $usuario;

    public function __construct(
        int $idUsuario,
        bool $activo = true,
        int $idCliente = 0,
        ?Usuario $usuario = null
    ) {
        $this->idCliente = $idCliente;
        $this->idUsuario = $idUsuario;
        $this->activo = $activo;
        $this->usuario = $usuario;
    }

    public function getIdCliente(): int { return $this->idCliente; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function isActivo(): bool { return $this->activo; }

    // Los datos personales viven en Usuario. Estos métodos se conservan
    // para que las APIs/vistas existentes sigan funcionando.
    public function getNombreCompleto(): string { return $this->usuario()->getNombreCompleto(); }
    public function getIdentificacion(): string { return $this->usuario()->getIdentificacion(); }
    public function getNumeroIdentificacion(): string { return $this->usuario()->getIdentificacion(); }
    public function getCorreo(): string { return $this->usuario()->getCorreo(); }
    public function getPasswordHash(): string { return $this->usuario()->getPasswordHash(); }
    public function getPerfilImagen(): ?string { return $this->usuario()->getPerfilImagen(); }
    public function getFotoPerfil(): ?string { return $this->usuario()->getPerfilImagen(); }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    private function usuario(): Usuario
    {
        if ($this->usuario === null) {
            throw new LogicException("El cliente no tiene cargados los datos de usuario");
        }
        return $this->usuario;
    }
}