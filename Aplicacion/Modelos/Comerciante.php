<?php

class Comerciante
{
    use ValidadorTexto;
    private int $idComerciante;
    private string $nombreCompleto;
    private string $alias;
    public readonly string $cedula;
    private string $correo;
    private string $passwordHash;
    private ?string $perfilImagen;
    private ?DateTime $fechaRegistro;
    private bool $activo;

    public function __construct(
        string $nombreCompleto,
        string $alias,
        string $cedula,
        string $correo,
        string $passwordHash,
        ?string $perfilImagen = null,
        bool $activo = true,
        int $idComerciante = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idComerciante = $idComerciante;
        $this->cedula = $cedula;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;
        $this->fechaRegistro = $fechaRegistro;

        $this->setNombreCompleto($nombreCompleto);
        $this->setAlias($alias);
        $this->setCorreo($correo);
        $this->setPerfilImagen($perfilImagen);
    }

    public function getIdComerciante(): int { return $this->idComerciante; }
    public function getNombreCompleto(): string { return $this->nombreCompleto; }
    public function getAlias(): string { return $this->alias; }
    public function getCedula(): string { return $this->cedula; }
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

    public function setAlias(string $alias): void
    {
        if (trim($alias) === '') {
            throw new InvalidArgumentException("El alias no puede estar vacío");
        }
        $this->validarSoloLetras($alias, "El alias");
        $this->alias = $alias;
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