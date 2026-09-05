<?php

require_once __DIR__ . "/../Comun/ValidarTexto.php";

class SuperAdmin
{
    use ValidadorTexto;

    private int $idSuperAdmin;
    private string $correo;
    private string $passwordHash;
    private string $nombreCompleto;
    private ?DateTime $fechaRegistro;
    private bool $activo;

    public function __construct(
        string $correo,
        string $passwordHash,
        string $nombreCompleto,
        bool $activo = true,
        int $idSuperAdmin = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idSuperAdmin = $idSuperAdmin;
        $this->passwordHash = $passwordHash;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;

        $this->setCorreo($correo);
        $this->setNombreCompleto($nombreCompleto);
    }

    public function getIdSuperAdmin(): int
    {
        return $this->idSuperAdmin;
    }
    public function getCorreo(): string
    {
        return $this->correo;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function getNombreCompleto(): string
    {
        return $this->nombreCompleto;
    }
    public function getFechaRegistro(): ?DateTime
    {
        return $this->fechaRegistro;
    }
    public function isActivo(): bool
    {
        return $this->activo;
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

    public function setNombreCompleto(string $nombreCompleto): void
    {
        if (trim($nombreCompleto) === '') {
            throw new InvalidArgumentException("El nombre no puede estar vacío");
        }
        $this->validarSoloLetras($nombreCompleto, "El nombre");
        $this->nombreCompleto = $nombreCompleto;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}