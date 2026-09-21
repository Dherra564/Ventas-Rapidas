<?php

require_once __DIR__ . "/../Comun/ValidarTexto.php";

class Usuario
{
    use ValidadorTexto;

    private int $idUsuario;
    private string $nombreCompleto;
    public readonly string $identificacion;
    private string $correo;
    private string $passwordHash;
    private ?string $perfilImagen;
    private ?DateTime $fechaRegistro;
    private bool $activo;

    public function __construct(
        string $nombreCompleto,
        string $identificacion,
        string $correo,
        string $passwordHash,
        ?string $perfilImagen = null,
        bool $activo = true,
        int $idUsuario = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idUsuario = $idUsuario;
        $this->identificacion = $identificacion;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;
        $this->fechaRegistro = $fechaRegistro;

        $this->setNombreCompleto($nombreCompleto);
        $this->setCorreo($correo);
        $this->setPerfilImagen($perfilImagen);
    }

    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getNombreCompleto(): string { return $this->nombreCompleto; }
    public function getIdentificacion(): string { return $this->identificacion; }
    public function getCorreo(): string { return $this->correo; }
    public function getPasswordHash(): string { return $this->passwordHash; }
    public function getPerfilImagen(): ?string { return $this->perfilImagen; }
    public function getFechaRegistro(): ?DateTime { return $this->fechaRegistro; }
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