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
        $this->idCliente = $idCliente;
        $this->idLocal = $idLocal;
        $this->activo = $activo;
    }

    public function getIdClienteLocal(): int { return $this->idClienteLocal; }
    public function getIdCliente(): int { return $this->idCliente; }
    public function getIdLocal(): int { return $this->idLocal; }
    public function isActivo(): bool { return $this->activo; }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}