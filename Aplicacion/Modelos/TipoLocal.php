<?php

class TipoLocal
{
    private int $idTipoLocal;
    private string $nombre;

    public function __construct(
        string $nombre,
        int $idTipoLocal = 0
    ) {
        $this->idTipoLocal = $idTipoLocal;

        $this->setNombre($nombre);
    }

    public function getIdTipoLocal(): int
    {
        return $this->idTipoLocal;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre del tipo de local no puede estar vacío");
        }
        $this->nombre = $nombre;
    }
}