<?php

class SuperAdminPasswordHistorico
{
    private int $idHistorico;
    private int $idSuperAdmin;
    private bool $exitoso;
    private ?DateTime $fecha;

    public function __construct(
        int $idSuperAdmin,
        bool $exitoso,
        int $idHistorico = 0,
        ?DateTime $fecha = null
    ) {
        $this->idSuperAdmin = $idSuperAdmin;
        $this->exitoso = $exitoso;
        $this->idHistorico = $idHistorico;
        $this->fecha = $fecha;
    }

    public function getIdHistorico(): int
    {
        return $this->idHistorico;
    }
    public function getIdSuperAdmin(): int
    {
        return $this->idSuperAdmin;
    }
    public function isExitoso(): bool
    {
        return $this->exitoso;
    }
    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }
}