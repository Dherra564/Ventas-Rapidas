<?php

class Ubicacion
{
    private const PROVINCIAS_VALIDAS = [
        'San José',
        'Alajuela',
        'Cartago',
        'Heredia',
        'Guanacaste',
        'Puntarenas',
        'Limón'
    ];

    private int $idUbicacion;
    private int $idLocal;
    private string $provincia;
    private string $canton;
    private string $distrito
    ;
    private string $direccionExacta;
    private ?string $referencia;
    private bool $activo;

    public function __construct(
        int $idLocal,
        string $provincia,
        string $canton,
        string $distrito,
        string $direccionExacta,
        ?string $referencia = null,
        bool $activo = true,
        int $idUbicacion = 0
    ) {
        $this->idUbicacion = $idUbicacion;
        $this->idLocal = $idLocal;

        $this->setProvincia($provincia);
        $this->setCanton($canton);
        $this->setDistrito($distrito);
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
    public function getProvincia(): string
    {
        return $this->provincia;
    }
    public function getCanton(): string
    {
        return $this->canton;
    }
    public function getDistrito(): string
    {
        return $this->distrito;
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

    public function setProvincia(string $provincia): void
    {
        if (!in_array($provincia, self::PROVINCIAS_VALIDAS, true)) {
            throw new InvalidArgumentException("Provincia inválida: $provincia");
        }
        $this->provincia = $provincia;
    }

    public function setCanton(string $canton): void
    {
        if (trim($canton) === '') {
            throw new InvalidArgumentException("El cantón no puede estar vacío");
        }
        $this->canton = $canton;
    }

    public function setDistrito(string $distrito): void
    {
        if (trim($distrito) === '') {
            throw new InvalidArgumentException("El distrito no puede estar vacío");
        }
        $this->distrito = $distrito;
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