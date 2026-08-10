<?php

class Producto
{
    private int $idProducto;
    private int $idLocal;
    private int $idTipoProducto;
    private string $nombre;
    private ?string $descripcion;
    private float $precioOriginal;
    private ?float $porcentajeDescuento;
    private int $cantidadDisponible;
    private bool $agotado;
    private ?string $imagen;
    private bool $activo;
    private ?DateTime $fechaCreacion;

    public function __construct(
        int $idLocal,
        int $idTipoProducto,
        string $nombre,
        float $precioOriginal,
        ?float $porcentajeDescuento = null,
        ?string $descripcion = null,
        int $cantidadDisponible = 0,
        ?string $imagen = null,
        bool $activo = true,
        int $idProducto = 0,
        ?DateTime $fechaCreacion = null
    ) {
        $this->idLocal = $idLocal;
        $this->idTipoProducto = $idTipoProducto;
        $this->idProducto = $idProducto;
        $this->activo = $activo;
        $this->fechaCreacion = $fechaCreacion;

        $this->setNombre($nombre);
        $this->setDescripcion($descripcion);
        $this->setPrecioOriginal($precioOriginal);
        $this->setPorcentajeDescuento($porcentajeDescuento);
        $this->setCantidadDisponible($cantidadDisponible);
        $this->setImagen($imagen);
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }
    public function getIdLocal(): int
    {
        return $this->idLocal;
    }
    public function getIdTipoProducto(): int
    {
        return $this->idTipoProducto;
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
    public function getPorcentajeDescuento(): ?float
    {
        return $this->porcentajeDescuento;
    }
    public function tieneDescuento(): bool
    {
        return $this->porcentajeDescuento !== null;
    }

    public function getPrecioFinal(): float
    {
        if (!$this->tieneDescuento()) {
            return $this->precioOriginal;
        }

        return round(
            $this->precioOriginal * (1 - ($this->porcentajeDescuento / 100)),
            2
        );
    }

    public function getCantidadDisponible(): int
    {
        return $this->cantidadDisponible;
    }
    public function isAgotado(): bool
    {
        return $this->agotado;
    }
    public function getImagen(): ?string
    {
        return $this->imagen;
    }
    public function isActivo(): bool
    {
        return $this->activo;
    }
    public function getFechaCreacion(): ?DateTime
    {
        return $this->fechaCreacion;
    }

    public function setNombre(string $nombre): void
    {
        if (trim($nombre) === '') {
            throw new InvalidArgumentException("El nombre del producto no puede estar vacío");
        }
        $this->nombre = $nombre;
    }

    public function setDescripcion(?string $descripcion): void
    {
        if ($descripcion !== null && trim($descripcion) === '') {
            throw new InvalidArgumentException("La descripción no puede estar vacía");
        }
        $this->descripcion = $descripcion;
    }

    public function setPrecioOriginal(float $precioOriginal): void
    {
        if ($precioOriginal <= 0) {
            throw new InvalidArgumentException("El precio original debe ser mayor a 0");
        }
        $this->precioOriginal = $precioOriginal;
    }

    public function setPorcentajeDescuento(?float $porcentajeDescuento): void
    {
        if ($porcentajeDescuento !== null) {
            if ($porcentajeDescuento <= 0 || $porcentajeDescuento >= 100) {
                throw new InvalidArgumentException(
                    "El porcentaje de descuento debe estar entre 0 y 100, sin llegar a ninguno de los dos"
                );
            }
        }
        $this->porcentajeDescuento = $porcentajeDescuento;
    }

    public function setCantidadDisponible(int $cantidadDisponible): void
    {
        if ($cantidadDisponible < 0) {
            throw new InvalidArgumentException("La cantidad no puede ser negativa");
        }
        $this->cantidadDisponible = $cantidadDisponible;
        $this->agotado = $cantidadDisponible <= 0;
    }

    public function setImagen(?string $imagen): void
    {
        $this->imagen = $imagen;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}