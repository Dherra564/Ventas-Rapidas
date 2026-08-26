<?php

class Ubicacion
{
    private int $idUbicacion;
    private ?int $idLocal;
    private ?int $idCliente;
    private int $idProvincia;
    private int $idCanton;
    private int $idDistrito;
    private string $direccionExacta;
    private ?string $referencia;
    private bool $activo;
    private ?float $latitud;
    private ?float $longitud;

    public function __construct(
        ?int $idLocal,
        int $idProvincia,
        int $idCanton,
        int $idDistrito,
        string $direccionExacta,
        ?string $referencia = null,
        ?int $idCliente = null,
        bool $activo = true,
        int $idUbicacion = 0,
        ?float $latitud = null,
        ?float $longitud = null
    ) {
        $this->idUbicacion = $idUbicacion;
        $this->idLocal = $idLocal;
        $this->idCliente = $idCliente;
        $this->idProvincia = $idProvincia;
        $this->idCanton = $idCanton;
        $this->idDistrito = $idDistrito;

        $this->setDireccionExacta($direccionExacta);
        $this->setReferencia($referencia);
        $this->setActivo($activo);
        $this->setLatitud($latitud);
        $this->setLongitud($longitud);
    }

    public function getIdUbicacion(): int { return $this->idUbicacion; }
    public function getIdLocal(): ?int { return $this->idLocal; }
    public function getIdCliente(): ?int { return $this->idCliente; }
    public function getIdProvincia(): int { return $this->idProvincia; }
    public function getIdCanton(): int { return $this->idCanton; }
    public function getIdDistrito(): int { return $this->idDistrito; }
    public function getDireccionExacta(): string { return $this->direccionExacta; }
    public function getReferencia(): ?string { return $this->referencia; }
    public function isActivo(): bool { return $this->activo; }
    public function getLatitud(): ?float { return $this->latitud; }
    public function getLongitud(): ?float { return $this->longitud; }

    public function tieneCoordenadas(): bool
    {
        return $this->latitud !== null && $this->longitud !== null;
    }

    public function setIdLocal(?int $idLocal): void { $this->idLocal = $idLocal; }
    public function setIdCliente(?int $idCliente): void { $this->idCliente = $idCliente; }

    public function tieneDuenoValido(): bool
    {
        $tieneLocal = $this->idLocal !== null && $this->idLocal > 0;
        $tieneCliente = $this->idCliente !== null && $this->idCliente > 0;
        return $tieneLocal xor $tieneCliente;
    }

    public function setDireccionExacta(string $direccionExacta): void
    {
        if (trim($direccionExacta) === '') {
            throw new InvalidArgumentException("La dirección exacta no puede estar vacía");
        }
        $this->direccionExacta = trim($direccionExacta);
    }

    public function setReferencia(?string $referencia): void
    {
        $referencia = $referencia !== null ? trim($referencia) : null;
        $this->referencia = $referencia === '' ? null : $referencia;
    }

    public function setActivo(bool $activo): void { $this->activo = $activo; }

    public function setLatitud(?float $latitud): void
    {
        if ($latitud !== null && ($latitud < -90 || $latitud > 90)) {
            throw new InvalidArgumentException("La latitud debe estar entre -90 y 90");
        }
        $this->latitud = $latitud;
    }

    public function setLongitud(?float $longitud): void
    {
        if ($longitud !== null && ($longitud < -180 || $longitud > 180)) {
            throw new InvalidArgumentException("La longitud debe estar entre -180 y 180");
        }
        $this->longitud = $longitud;
    }
}