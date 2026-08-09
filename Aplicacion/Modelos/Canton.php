<?php

class Canton
{
    private int $idCanton;
    private int $idProvincia;
    private string $nombre;
    private bool $activo;

    public function __construct(
        int $idProvincia,
        string $nombre,
        bool $activo = true,
        int $idCanton = 0
    ) {
        $this->idProvincia = $idProvincia;
        $this->idCanton = $idCanton;
        $this->activo = $activo;

        $this->setNombre($nombre);
    }

    public function getIdCanton(): int
    {
        return $this->idCanton;
    }

    public function getIdProvincia(): int
    {
        return $this->idProvincia;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre del cantón no puede estar vacío");
        }
        $this->nombre = $nombre;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}