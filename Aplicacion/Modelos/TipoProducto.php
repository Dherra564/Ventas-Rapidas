<?php

class TipoProducto
{
    private int $idTipoProducto;
    private string $nombre;

    public function __construct(
        string $nombre,
        int $idTipoProducto = 0
    ) {
        $this->idTipoProducto = $idTipoProducto;

        $this->setNombre($nombre);
    }

    public function getIdTipoProducto(): int
    {
        return $this->idTipoProducto;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre del tipo de producto no puede estar vacío");
        }
        $this->nombre = $nombre;
    }
}