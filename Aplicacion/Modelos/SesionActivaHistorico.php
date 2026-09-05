<?php

class SesionActivaHistorico
{
    private int $idSesionActivoHistorico;
    private int $idSesion;
    private int $idLocal;
    private bool $valorAnterior;
    private bool $valorNuevo;
    private ?DateTime $fecha;
    private bool $activo;

    public function __construct(
        int $idSesion,
        int $idLocal,
        bool $valorAnterior,
        bool $valorNuevo,
        bool $activo = true,
        int $idSesionActivoHistorico = 0,
        ?DateTime $fecha = null
    ) {
        $this->idSesionActivoHistorico = $idSesionActivoHistorico;
        $this->idSesion = $idSesion;
        $this->idLocal = $idLocal;
        $this->valorAnterior = $valorAnterior;
        $this->valorNuevo = $valorNuevo;
        $this->activo = $activo;
        $this->fecha = $fecha;
    }

    public function getIdSesionActivoHistorico(): int
    {
        return $this->idSesionActivoHistorico;
    }
    public function getIdSesion(): int
    {
        return $this->idSesion;
    }
    public function getIdLocal(): int
    {
        return $this->idLocal;
    }
    public function isValorAnterior(): bool
    {
        return $this->valorAnterior;
    }
    public function isValorNuevo(): bool
    {
        return $this->valorNuevo;
    }
    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }
    public function isActivo(): bool
    {
        return $this->activo;
    }
}