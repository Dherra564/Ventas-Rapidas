<?php

class Distrito
{
    private int $idDistrito;
    private int $idCanton;
    private string $nombre;
    private bool $activo;

    public function __construct(
        int $idCanton,
        string $nombre,
        bool $activo = true,
        int $idDistrito = 0
    ) {
        $this->idCanton = $idCanton;
        $this->idDistrito = $idDistrito;
        $this->activo = $activo;

        $this->setNombre($nombre);
    }

    public function getIdDistrito(): int
    {
        return $this->idDistrito;
    }

    public function getIdCanton(): int
    {
        return $this->idCanton;
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
            throw new InvalidArgumentException("El nombre del distrito no puede estar vacío");
        }
        $this->nombre = $nombre;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}