<?php

class ComercianteLocal
{
    private int $idComercianteLocal;
    private int $idComerciante;
    private int $idLocal;
    private bool $activo;

    public function __construct(
        int $idComerciante,
        int $idLocal,
        bool $activo = true,
        int $idComercianteLocal = 0

    ) {
        $this->idComercianteLocal = $idComercianteLocal;
        $this->idComerciante = $idComerciante;
        $this->idLocal = $idLocal;
        $this->activo = $activo;
    }

    public function getIdComercianteLocal(): int
    {
        return $this->idComercianteLocal;
    }

    public function getIdComerciante(): int
    {
        return $this->idComerciante;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdLocal(int $idLocal): void
    {
        $this->idLocal = $idLocal;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}