<?php

class HistorialCampo
{
    private int $idHistorial;
    private int $idEntidad;
    private $valorAnterior;
    private $valorNuevo;
    private ?DateTime $fecha;

    public function __construct(
        int $idEntidad,
        $valorAnterior,
        $valorNuevo,
        int $idHistorial = 0,
        ?DateTime $fecha = null
    ) {
        $this->idEntidad = $idEntidad;
        $this->valorAnterior = $valorAnterior;
        $this->valorNuevo = $valorNuevo;
        $this->idHistorial = $idHistorial;
        $this->fecha = $fecha;
    }

    public function getIdHistorial(): int
    {
        return $this->idHistorial;
    }
    public function getIdEntidad(): int
    {
        return $this->idEntidad;
    }
    public function getValorAnterior()
    {
        return $this->valorAnterior;
    }
    public function getValorNuevo()
    {
        return $this->valorNuevo;
    }
    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }
}