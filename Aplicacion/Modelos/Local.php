<?php

class Local
{
    use ValidadorTexto;
    private int $idLocal;
    private int $idTipoLocal;
    private string $nombreLocal;
    private ?string $descripcion;
    private string $telefono;
    private string $correo;
    private ?string $logo;
    private bool $activo;
    private ?DateTime $fechaRegistro;

    public function __construct(
        int $idTipoLocal,
        string $nombreLocal,
        string $telefono,
        string $correo,
        ?string $descripcion = null,
        ?string $logo = null,
        bool $activo = true,
        int $idLocal = 0,
        ?DateTime $fechaRegistro = null
    ) {
        $this->idTipoLocal = $idTipoLocal;
        $this->idLocal = $idLocal;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;

        $this->setNombreLocal($nombreLocal);
        $this->setTelefono($telefono);
        $this->setCorreo($correo);
        $this->setDescripcion($descripcion);
        $this->setLogo($logo);
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }
    public function getIdTipoLocal(): int
    {
        return $this->idTipoLocal;
    }
    public function getNombreLocal(): string
    {
        return $this->nombreLocal;
    }
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
    public function getTelefono(): string
    {
        return $this->telefono;
    }
    public function getCorreo(): string
    {
        return $this->correo;
    }
    public function getLogo(): ?string
    {
        return $this->logo;
    }
    public function isActivo(): bool
    {
        return $this->activo;
    }
    public function getFechaRegistro(): ?DateTime
    {
        return $this->fechaRegistro;
    }

    public function setNombreLocal(string $nombreLocal): void
    {
        if (trim($nombreLocal) === '') {
            throw new InvalidArgumentException("El nombre del local no puede estar vacío");
        }
        $this->nombreLocal = $nombreLocal;
    }

    public function setDescripcion(?string $descripcion): void
    {
        if ($descripcion !== null && trim($descripcion) === '') {
            throw new InvalidArgumentException("La descripción no puede estar vacía");
        }
        $this->descripcion = $descripcion;
    }

    public function setTelefono(string $telefono): void
    {
        if (trim($telefono) === '') {
            throw new InvalidArgumentException("El teléfono no puede estar vacío");
        }
        $this->validarTelefono($telefono);
        $this->telefono = $telefono;
    }

    public function setCorreo(string $correo): void
    {
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Correo inválido: $correo");
        }
        $this->correo = $correo;
    }

    public function setLogo(?string $logo): void
    {
        $this->logo = $logo;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}