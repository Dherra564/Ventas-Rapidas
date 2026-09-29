<?php

require_once __DIR__ . "/CarritoCompraDetalle.php";

class CarritoCompra
{
    private int $idCarritoCompra;
    private int $idCliente;
    private int $idLocal;
    private ?DateTime $registroFecha;
    private ?DateTime $actualizacionFecha;
    private array $detalles = [];

    public function __construct(
        int $idCliente,
        int $idLocal,
        int $idCarritoCompra = 0,
        ?DateTime $registroFecha = null,
        ?DateTime $actualizacionFecha = null
    ) {
        if ($idCliente <= 0) {
            throw new InvalidArgumentException("Cliente no válido");
        }

        if ($idLocal <= 0) {
            throw new InvalidArgumentException("Local no válido");
        }

        $this->idCliente = $idCliente;
        $this->idLocal = $idLocal;
        $this->idCarritoCompra = $idCarritoCompra;
        $this->registroFecha = $registroFecha;
        $this->actualizacionFecha = $actualizacionFecha;
    }

    public function getIdCarritoCompra(): int
    {
        return $this->idCarritoCompra;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function getRegistroFecha(): ?DateTime
    {
        return $this->registroFecha;
    }

    public function getActualizacionFecha(): ?DateTime
    {
        return $this->actualizacionFecha;
    }

    public function getDetalles(): array
    {
        return array_values($this->detalles);
    }

    public function agregarDetalle(CarritoCompraDetalle $detalle): void
    {
        $this->detalles[$detalle->getIdProducto()] = $detalle;
    }

    public function obtenerDetalle(int $idProducto): ?CarritoCompraDetalle
    {
        return $this->detalles[$idProducto] ?? null;
    }

    public function cantidadDeProducto(int $idProducto): int
    {
        return $this->obtenerDetalle($idProducto)?->getCantidad() ?? 0;
    }

    public function estaVacio(): bool
    {
        return empty($this->detalles);
    }

    public function getCantidadProductos(): int
    {
        return count($this->detalles);
    }

    public function getCantidadArticulos(): int
    {
        return array_sum(array_map(fn(CarritoCompraDetalle $d) => $d->getCantidad(), $this->detalles));
    }

    public function aItemsDePedido(): array
    {
        return array_map(fn(CarritoCompraDetalle $d) => [
            "idProducto" => $d->getIdProducto(),
            "cantidad" => $d->getCantidad()
        ], $this->getDetalles());
    }
}