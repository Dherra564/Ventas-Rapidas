<?php


class Cliente
{
    private int $idCliente;
    private string $nombre;
    private string $apellido;
    private string $cedula;
    private string $correo;
    private string $passwordHash
    ;
    private ?string $fechaRegistro;

    public function __construct(
        string $nombre,
        string $apellido,
        string $cedula,
        string $correo,
        string $passwordHash
        ,
        int $idCliente = 0,
        ?string $fechaRegistro = null
    ) {
        $this->idCliente = $idCliente;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->cedula = $cedula;
        $this->correo = $correo;
        $this->passwordHash = $passwordHash;
        $this->fechaRegistro = $fechaRegistro;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
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
    public function getFechaRegistro(): ?string
    {
        return $this->fechaRegistro;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setApellido(string $apellido): void
    {
        $this->apellido = $apellido;
    }
    public function setCorreo(string $correo): void
    {
        $this->correo = $correo;
    }
    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }
}