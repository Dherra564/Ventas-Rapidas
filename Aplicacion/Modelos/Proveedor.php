<?php

class Proveedor
{
    private int $idProveedor;
    private string $nombre;
    private string $apellido;
    public readonly string $cedula;
    private string $correo;
    private string $passwordHash
    ;
    private ?DateTime $fechaRegistro;
    private bool $activo;

    public function __construct(
        string $nombre,
        string $apellido,
        string $cedula,
        string $correo,
        string $passwordHash,
        bool $activo = true,
        int $idProveedor = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idProveedor = $idProveedor;
        $this->cedula = $cedula;
        $this->activo = $activo;
        $this->passwordHash = $passwordHash;
        $this->fechaRegistro = $fechaRegistro;

        $this->setNombre($nombre);
        $this->setApellido($apellido);
        $this->setCorreo($correo);
    }

    public function getIdProveedor(): int
    {
        return $this->idProveedor;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function getApellido(): string
    {
        return $this->apellido;
    }
    public function getCedula(): string
    {
        return $this->cedula;
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

    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre no puede estar vacío");
        }
        $this->nombre = $nombre;
    }

    public function setApellido(string $apellido): void
    {
        if (trim($apellido) === '') {
            throw new InvalidArgumentException("El apellido no puede estar vacío");
        }
        $this->apellido = $apellido;
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