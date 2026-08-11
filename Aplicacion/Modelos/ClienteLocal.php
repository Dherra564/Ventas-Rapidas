<?php

class ClienteLocal
{
    private int $idClienteLocal;
    private int $idCliente;
    private int $idLocal;
    private bool $activo;

    public function __construct(
        int $idCliente,
        int $idLocal,
        bool $activo = true,
        int $idClienteLocal = 0
    ) {
        $this->idClienteLocal = $idClienteLocal;
        $this->setIdCliente($idCliente);
        $this->setIdLocal($idLocal);
        $this->activo = $activo;
    }

    public function getIdClienteLocal(): int
    {
        return $this->idClienteLocal;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
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