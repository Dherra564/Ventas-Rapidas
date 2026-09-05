<?php

class UbicacionHistorial
{
    public const TIPO_CLIENTE = 'Cliente';
    public const TIPO_COMERCIANTE = 'Comerciante';

    private const TIPOS_VALIDOS = [self::TIPO_CLIENTE, self::TIPO_COMERCIANTE];

    private int $idHistorialUbicacion;
    private ?int $idUbicacion;
    private int $idUsuario;
    private string $tipoUsuario;
    private ?float $latitudAnterior;
    private ?float $longitudAnterior;
    private float $latitudNueva;
    private float $longitudNueva;
    private ?DateTime $fecha;

    public function __construct(
        ?int $idUbicacion,
        int $idUsuario,
        string $tipoUsuario,
        ?float $latitudAnterior,
        ?float $longitudAnterior,
        float $latitudNueva,
        float $longitudNueva,
        int $idHistorialUbicacion = 0,
        ?DateTime $fecha = null
    ) {
        $this->idHistorialUbicacion = $idHistorialUbicacion;
        $this->idUbicacion = $idUbicacion;
        $this->fecha = $fecha;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
        $this->setLatitudAnterior($latitudAnterior);
        $this->setLongitudAnterior($longitudAnterior);
        $this->setLatitudNueva($latitudNueva);
        $this->setLongitudNueva($longitudNueva);
    }

    public function getIdHistorialUbicacion(): int
    {
        return $this->idHistorialUbicacion;
    }

    public function getIdUbicacion(): ?int
    {
        return $this->idUbicacion;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
    }

    public function getLatitudAnterior(): ?float
    {
        return $this->latitudAnterior;
    }

    public function getLongitudAnterior(): ?float
    {
        return $this->longitudAnterior;
    }

    public function getLatitudNueva(): float
    {
        return $this->latitudNueva;
    }

    public function getLongitudNueva(): float
    {
        return $this->longitudNueva;
    }

    public function getFecha(): ?DateTime
    {
        return $this->fecha;
    }

    public function setIdUsuario(int $idUsuario): void
    {
        if ($idUsuario <= 0) {
            throw new InvalidArgumentException("El ID de usuario debe ser mayor a cero");
        }
        $this->idUsuario = $idUsuario;
    }

    public function setTipoUsuario(string $tipoUsuario): void
    {
        if (!in_array($tipoUsuario, self::TIPOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Tipo de usuario inválido: $tipoUsuario");
        }
        $this->tipoUsuario = $tipoUsuario;
    }

    public function setLatitudAnterior(?float $latitudAnterior): void
    {
        if ($latitudAnterior !== null && ($latitudAnterior < -90 || $latitudAnterior > 90)) {
            throw new InvalidArgumentException("La latitud anterior debe estar entre -90 y 90");
        }
        $this->latitudAnterior = $latitudAnterior;
    }

    public function setLongitudAnterior(?float $longitudAnterior): void
    {
        if ($longitudAnterior !== null && ($longitudAnterior < -180 || $longitudAnterior > 180)) {
            throw new InvalidArgumentException("La longitud anterior debe estar entre -180 y 180");
        }
        $this->longitudAnterior = $longitudAnterior;
    }

    public function setLatitudNueva(float $latitudNueva): void
    {
        if ($latitudNueva < -90 || $latitudNueva > 90) {
            throw new InvalidArgumentException("La latitud debe estar entre -90 y 90");
        }
        $this->latitudNueva = $latitudNueva;
    }

    public function setLongitudNueva(float $longitudNueva): void
    {
        if ($longitudNueva < -180 || $longitudNueva > 180) {
            throw new InvalidArgumentException("La longitud debe estar entre -180 y 180");
        }
        $this->longitudNueva = $longitudNueva;
    }
}