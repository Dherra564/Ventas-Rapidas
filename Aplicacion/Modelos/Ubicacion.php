<?php

class Ubicacion
{
    private int $idUbicacion;
    private int $idLocal;
    private int $idProvincia;
    private int $idCanton;
    private int $idDistrito;
    private string $direccionExacta;
    private ?string $referencia;
    private bool $activo;

    public function __construct(
        int $idLocal,
        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia = null,
        bool $activo = true,
        int $idUbicacion = 0
    ) {
        $this->idUbicacion = $idUbicacion;
        $this->idLocal = $idLocal;
        $this->idProvincia = $idProvincia;
        $this->idCanton = $idCanton;
        $this->idDistrito = $idDistrito;

        $this->setDireccionExacta($direccionExacta);
        $this->setReferencia($referencia);
        $this->setActivo($activo);
    }

    public function getIdUbicacion(): int
    {
        return $this->idUbicacion;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function getIdProvincia(): int
    {
        return $this->idProvincia;
    }

    public function getIdCanton(): int
    {
        return $this->idCanton;
    }

    public function getIdDistrito(): int
    {
        return $this->idDistrito;
    }

    public function getDireccionExacta(): string
    {
        return $this->direccionExacta;
    }

    public function getReferencia(): ?string
    {
        return $this->referencia;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdLocal(int $idLocal): void
    {
        $this->idLocal = $idLocal;
    }

    public function setDireccionExacta(string $direccionExacta): void
    {
        if (trim($direccionExacta) === '') {
            throw new InvalidArgumentException("La dirección exacta no puede estar vacía");
        }
        $this->direccionExacta = $direccionExacta;
    }

    public function setReferencia(?string $referencia): void
    {
        if ($referencia !== null && trim($referencia) === '') {
            throw new InvalidArgumentException("La referencia no puede estar vacía");
        }
        $this->referencia = $referencia;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}