<?php

class RegistroCompra
{
    private int $idRegistroCompra;
    private int $idCliente;
    private int $idLocal;
    private ?DateTime $fechaCompra;
    private bool $activo;

    public function __construct(
        int $idCliente,
        int $idLocal,
        bool $activo = true,
        int $idRegistroCompra = 0,
        ?DateTime $fechaCompra = null
    ) {
        $this->idRegistroCompra = $idRegistroCompra;
        $this->activo = $activo;
        $this->fechaCompra = $fechaCompra;

        $this->setIdCliente($idCliente);
        $this->setIdLocal($idLocal);
    }

    public function getIdRegistroCompra(): int
    {
        return $this->idRegistroCompra;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function getFechaCompra(): ?DateTime
    {
        return $this->fechaCompra;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdCliente(int $idCliente): void
    {
        if ($idCliente <= 0) {
            throw new InvalidArgumentException("El ID de cliente debe ser mayor a cero");
        }
        $this->idCliente = $idCliente;
    }

    public function setIdLocal(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new InvalidArgumentException("El ID de local debe ser mayor a cero");
        }
        $this->idLocal = $idLocal;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}