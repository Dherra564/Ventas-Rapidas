<?php

class DetallePedido
{
    private int $idPedidoDetalle;
    private int $idPedido;
    private int $idProducto;
    private string $productoNombre;
    private int $cantidad;
    private float $precio;
    private ?float $descuentoPorcentaje;
    private float $precioUnitario;
    private bool $activo;

    public function __construct(
        int $idProducto,
        string $productoNombre,
        int $cantidad,
        float $precio,
        ?float $descuentoPorcentaje,
        float $precioUnitario,
        bool $activo = true,
        int $idPedido = 0,
        int $idPedidoDetalle = 0
    ) {
        $this->idPedidoDetalle = $idPedidoDetalle;
        $this->idPedido = $idPedido;
        $this->activo = $activo;

        $this->setIdProducto($idProducto);
        $this->setProductoNombre($productoNombre);
        $this->setCantidad($cantidad);
        $this->setPrecio($precio);
        $this->setDescuentoPorcentaje($descuentoPorcentaje);
        $this->setPrecioUnitario($precioUnitario);
    }

    public function getIdPedidoDetalle(): int
    {
        return $this->idPedidoDetalle;
    }

    public function getIdPedido(): int
    {
        return $this->idPedido;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getProductoNombre(): string
    {
        return $this->productoNombre;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function getPrecio(): float
    {
        return $this->precio;
    }

    public function getDescuentoPorcentaje(): ?float
    {
        return $this->descuentoPorcentaje;
    }

    public function getPrecioUnitario(): float
    {
        return $this->precioUnitario;
    }

    public function getSubtotal(): float
    {
        return round($this->precioUnitario * $this->cantidad, 2);
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdPedido(int $idPedido): void
    {
        $this->idPedido = $idPedido;
    }

    public function setIdProducto(int $idProducto): void
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException("El producto del detalle no es válido");
        }
        $this->idProducto = $idProducto;
    }

    public function setProductoNombre(string $productoNombre): void
    {
        if (trim($productoNombre) === '') {
            throw new InvalidArgumentException("El nombre del producto no puede estar vacío");
        }
        $this->productoNombre = $productoNombre;
    }

    public function setCantidad(int $cantidad): void
    {
        if ($cantidad <= 0) {
            throw new InvalidArgumentException("La cantidad debe ser mayor a 0");
        }
        $this->cantidad = $cantidad;
    }

    public function setPrecio(float $precio): void
    {
        if ($precio <= 0) {
            throw new InvalidArgumentException("El precio debe ser mayor a 0");
        }
        $this->precio = $precio;
    }

    public function setDescuentoPorcentaje(?float $descuentoPorcentaje): void
    {
        if ($descuentoPorcentaje !== null && ($descuentoPorcentaje <= 0 || $descuentoPorcentaje >= 100)) {
            throw new InvalidArgumentException("El porcentaje de descuento debe estar entre 0 y 100");
        }
        $this->descuentoPorcentaje = $descuentoPorcentaje;
    }

    public function setPrecioUnitario(float $precioUnitario): void
    {
        if ($precioUnitario < 0) {
            throw new InvalidArgumentException("El precio unitario no puede ser negativo");
        }
        $this->precioUnitario = $precioUnitario;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}