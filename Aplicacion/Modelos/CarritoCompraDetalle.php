<?php

class CarritoCompraDetalle
{
    public const CANTIDAD_MAXIMA = 99;

    private int $idCarritoCompraDetalle;
    private int $idCarritoCompra;
    private int $idProducto;
    private int $cantidad;
    private ?DateTime $registroFecha;

    public function __construct(
        int $idProducto,
        int $cantidad,
        int $idCarritoCompra = 0,
        int $idCarritoCompraDetalle = 0,
        ?DateTime $registroFecha = null
    ) {
        $this->idCarritoCompraDetalle = $idCarritoCompraDetalle;
        $this->idCarritoCompra = $idCarritoCompra;
        $this->registroFecha = $registroFecha;

        $this->setIdProducto($idProducto);
        $this->setCantidad($cantidad);
    }

    public function getIdCarritoCompraDetalle(): int
    {
        return $this->idCarritoCompraDetalle;
    }

    public function getIdCarritoCompra(): int
    {
        return $this->idCarritoCompra;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function getRegistroFecha(): ?DateTime
    {
        return $this->registroFecha;
    }

    public function setIdProducto(int $idProducto): void
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException("Producto no válido");
        }
        $this->idProducto = $idProducto;
    }

    public function setCantidad(int $cantidad): void
    {
        if ($cantidad < 1) {
            throw new InvalidArgumentException("La cantidad debe ser un número entero mayor a 0");
        }

        if ($cantidad > self::CANTIDAD_MAXIMA) {
            throw new InvalidArgumentException(
                "Puedes tener como máximo " . self::CANTIDAD_MAXIMA . " unidades de un mismo producto en el carrito"
            );
        }

        $this->cantidad = $cantidad;
    }
}