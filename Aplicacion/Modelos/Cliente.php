<?php

class Cliente
{
    private int $idCliente;
    private string $nombreCompleto;
    private readonly string $numeroIdentificacion;
    private string $fotoPerfil;
    private string $correo;
    private string $passwordHash;
    private bool $activo;

    public function __construct(
        string $nombreCompleto,
        string $numeroIdentificacion,
        string $correo,
        string $passwordHash,
        string $fotoPerfil = '',
        bool $activo = true,
        int $idCliente = 0
    ) {
        $this->idCliente = $idCliente;
        $this->numeroIdentificacion = $numeroIdentificacion;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;

        $this->setNombreCompleto($nombreCompleto);
        $this->setCorreo($correo);
        $this->setFotoPerfil($fotoPerfil);
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }
    public function getNombreCompleto(): string
    {
        return $this->nombreCompleto;
    }
    public function getNumeroIdentificacion(): string
    {
        return $this->numeroIdentificacion;
    }
    public function getFotoPerfil(): string
    {
        return $this->fotoPerfil;
    }
    public function getCorreo(): string
    {
        return $this->correo;
    }
    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }
    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setNombreCompleto(string $nombreCompleto): void
    {
        if (trim($nombreCompleto) === '') {
            throw new InvalidArgumentException("El nombre no puede estar vacío");
        }
        $this->nombreCompleto = $nombreCompleto;
    }

    public function setCorreo(string $correo): void
    {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Correo inválido: $correo");
        }
        $this->correo = $correo;
    }

    public function setFotoPerfil(string $fotoPerfil): void
    {
        $this->fotoPerfil = $fotoPerfil;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}