<?php

class ProductoLocal
{
    private int $idProductoLocal;
    private int $idProducto;
    private int $idLocal;
    private bool $activo;

    public function __construct(
        int $idProducto,
        int $idLocal,
        bool $activo = true,
        int $idProductoLocal = 0
    ) {
        $this->idProductoLocal = $idProductoLocal;
        $this->setIdProducto($idProducto);
        $this->setIdLocal($idLocal);
        $this->activo = $activo;
    }

    public function getIdProductoLocal(): int
    {
        return $this->idProductoLocal;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdProducto(int $idProducto): void
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException("El ID de producto debe ser mayor a cero");
        }
        $this->idProducto = $idProducto;
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