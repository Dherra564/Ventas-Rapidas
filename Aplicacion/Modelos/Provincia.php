<?php

class Provincia
{
    private int $idProvincia;
    private string $nombre;
    private bool $activo;

    public function __construct(
        string $nombre,
        bool $activo = true,
        int $idProvincia = 0
    ) {
        $this->idProvincia = $idProvincia;
        $this->activo = $activo;

        $this->setNombre($nombre);
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
            throw new InvalidArgumentException("El nombre de la provincia no puede estar vacío");
        }
        $this->nombre = $nombre;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}