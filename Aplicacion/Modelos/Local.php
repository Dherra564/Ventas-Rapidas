<?php

class Local
{
    private int $idLocal;
    private int $idProveedor;
    private string $nombreLocal;
    private ?string $descripcion;
    private bool $activo;
    private ?DateTime $fechaCreacion
    ;

    public function __construct(
        int $idProveedor,
        string $nombreLocal,
        ?string $descripcion = null,
        bool $activo = true,
        int $idLocal = 0,
        ?DateTime $fechaCreacion = null
    ) {
        $this->idProveedor = $idProveedor;
        $this->nombreLocal = $nombreLocal;
        $this->descripcion = $descripcion;
        $this->activo = $activo;
        $this->idLocal = $idLocal;
        $this->fechaCreacion = $fechaCreacion;
    }

    public function getIdLocal(): int { return $this->idLocal; }
    public function getIdProveedor(): int { return $this->idProveedor; }
    public function getNombreLocal(): string { return $this->nombreLocal; }
    public function getDescripcion(): ?string { return $this->descripcion; }

    public function isActivo(): bool { return $this->activo; }
    public function getFechaCreacion(): ?DateTime { return $this->fechaCreacion; }

    public function setNombreLocal(string $nombreLocal): void { $this->nombreLocal = $nombreLocal; }
    public function setDescripcion(?string $descripcion): void { $this->descripcion = $descripcion; }
    public function setActivo(bool $activo): void { $this->activo = $activo; }
}