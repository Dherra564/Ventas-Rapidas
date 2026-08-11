<?php

class Comerciante
{
    private int $idComerciante;
    private string $nombreCompleto;
    private string $alias;
    private readonly string $numeroIdentificacion;
    private string $fotoPerfil;
    private string $correo;
    private string $passwordHash;
    private ?DateTime $fechaRegistro;
    private bool $activo;

    public function __construct(
        string $nombreCompleto,
        string $alias,
        string $numeroIdentificacion,
        string $correo,
        string $passwordHash,
        string $fotoPerfil = '',
        bool $activo = true,
        int $idComerciante = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idComerciante = $idComerciante;
        $this->numeroIdentificacion = $numeroIdentificacion;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;
        $this->fechaRegistro = $fechaRegistro;

        $this->setNombreCompleto($nombreCompleto);
        $this->setAlias($alias);
        $this->setCorreo($correo);
        $this->setFotoPerfil($fotoPerfil);
    }

    public function getIdComerciante(): int
    {
        return $this->idComerciante;
    }
    public function getNombreCompleto(): string
    {
        return $this->nombreCompleto;
    }
    public function getAlias(): string
    {
        return $this->alias;
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
    public function getFechaRegistro(): ?DateTime
    {
        return $this->fechaRegistro;
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

    public function setAlias(string $alias): void
    {
        if (trim($alias) === '') {
            throw new InvalidArgumentException("El alias no puede estar vacío");
        }
        $this->alias = $alias;
    }

    public function setFotoPerfil(string $fotoPerfil): void
    {
        $this->fotoPerfil = $fotoPerfil;
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
    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}