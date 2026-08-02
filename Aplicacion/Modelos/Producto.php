<?php

class Producto
{
    private int $idProducto;
    private int $idLocal;
    private string $nombre;
    private ?string $descripcion;
    private float $precioOriginal;
    private float $precioDescuento;
    private int $cantidadDisponible
    ;
    private bool $agotado;
    private ?DateTime $fechaCreacion;

    public function __construct(
        int $idLocal,
        string $nombre,
        float $precioOriginal,
        float $precioDescuento,
        ?string $descripcion = null,
        int $cantidadDisponible = 0,
        bool $agotado = false,
        int $idProducto = 0,
        ?DateTime $fechaCreacion = null
    ) {
        $this->idLocal = $idLocal;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->idProducto = $idProducto;
        $this->fechaCreacion = $fechaCreacion;

        $this->setPrecioOriginal($precioOriginal);
        $this->setPrecioDescuento($precioDescuento);
        $this->setCantidadDisponible($cantidadDisponible);
        $this->agotado = $agotado;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }
    public function getIdLocal(): int
    {
        return $this->idLocal;
    }
    public function getNombre(): string
    {
        return $this->nombre;
    }
    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
    public function getPrecioOriginal(): float
    {
        return $this->precioOriginal;
    }
    public function getPrecioDescuento(): float
    {
        return $this->precioDescuento;
    }
    public function getCantidadDisponible(): int
    {
        return $this->cantidadDisponible;
    }
    public function isAgotado(): bool
    {
        return $this->agotado;
    }
    public function getFechaCreacion(): ?DateTime
    {
        return $this->fechaCreacion;
    }

    public function setNombre(string $nombre): void
    {
        $this->nombre = $nombre;
    }
    public function setDescripcion(?string $descripcion): void
    {
        $this->descripcion = $descripcion;
    }

    public function setPrecioOriginal(float $precioOriginal): void
    {
        if ($precioOriginal <= 0) {
            throw new InvalidArgumentException("El precio original debe ser mayor a 0");
        }
        $this->precioOriginal = $precioOriginal;
    }

    public function setPrecioDescuento(float $precioDescuento): void
    {
        if ($precioDescuento <= 0) {
            throw new InvalidArgumentException("El precio de descuento debe ser mayor a 0");
        }
        if (isset($this->precioOriginal) && $precioDescuento >= $this->precioOriginal) {
            throw new InvalidArgumentException("El precio de descuento debe ser menor al precio original");
        }
        $this->precioDescuento = $precioDescuento;
    }

    public function setCantidadDisponible(int $cantidadDisponible): void
    {
        if ($cantidadDisponible < 0) {
            throw new InvalidArgumentException("La cantidad no puede ser negativa");
        }
        $this->cantidadDisponible = $cantidadDisponible;
        $this->agotado = $cantidadDisponible <= 0;
    }

    public function setAgotado(bool $agotado): void
    {
        $this->agotado = $agotado;
    }
}