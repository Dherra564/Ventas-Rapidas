<?php

require_once __DIR__ . "/../Comun/ValidarTexto.php";

class Cliente
{
     use ValidadorTexto;
    private int $idCliente;
    private string $nombreCompleto;
    public readonly string $identificacion;
    private string $correo;
    private string $passwordHash;
    private ?string $perfilImagen;
    private bool $activo;

    public function __construct(
        string $nombreCompleto,
        string $identificacion,
        string $correo,
        string $passwordHash,
        ?string $perfilImagen = null,
        bool $activo = true,
        int $idCliente = 0
    ) {
        $this->idCliente = $idCliente;
        $this->identificacion = $identificacion;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;

        $this->setNombreCompleto($nombreCompleto);
        $this->setCorreo($correo);
        $this->setPerfilImagen($perfilImagen);
    }

    public function getIdCliente(): int { return $this->idCliente; }
    public function getNombreCompleto(): string { return $this->nombreCompleto; }
    public function getIdentificacion(): string { return $this->identificacion; }
    // Alias conservado para las APIs/vistas existentes.
    public function getNumeroIdentificacion(): string { return $this->identificacion; }
    public function getCorreo(): string { return $this->correo; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getPerfilImagen(): ?string { return $this->perfilImagen; }
    // Alias conservado para las APIs/vistas existentes.
    public function getFotoPerfil(): ?string { return $this->perfilImagen; }
    public function isActivo(): bool { return $this->activo; }

   public function setNombreCompleto(string $nombreCompleto): void
    {
        if (trim($nombreCompleto) === '') {
            throw new InvalidArgumentException("El nombre no puede estar vacío");
        }
        $this->validarSoloLetras($nombreCompleto, "El nombre");
        $this->nombreCompleto = $nombreCompleto;
    }

    public function setCorreo(string $correo): void
    {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Correo inválido: $correo");
        }
        $this->correo = $correo;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setPerfilImagen(?string $perfilImagen): void
    {
        $this->perfilImagen = $perfilImagen;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}