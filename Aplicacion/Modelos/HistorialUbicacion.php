<?php

class HistorialUbicacion
{
    public const TIPO_CLIENTE = 'Cliente';
    public const TIPO_COMERCIANTE = 'Comerciante';

    private const TIPOS_VALIDOS = [self::TIPO_CLIENTE, self::TIPO_COMERCIANTE];

    private int $idHistorialUbicacion;
    private ?int $idUbicacion;
    private int $idUsuario;
    private string $tipoUsuario;
    private string $campo;
    private ?string $valorAnterior;
    private string $valorNuevo;
    private ?DateTime $fecha;
    private bool $activo;

    public function __construct(
        ?int $idUbicacion,
        int $idUsuario,
        string $tipoUsuario,
        string $campo,
        ?string $valorAnterior,
        string $valorNuevo,
        bool $activo = true,
        int $idHistorialUbicacion = 0,
        ?DateTime $fecha = null
    ) {
        $this->idHistorialUbicacion = $idHistorialUbicacion;
        $this->idUbicacion = $idUbicacion;
        $this->activo = $activo;
        $this->fecha = $fecha;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
        $this->setCampo($campo);
        $this->setValorAnterior($valorAnterior);
        $this->setValorNuevo($valorNuevo);
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

    public function getCampo(): string
    {
        return $this->campo;
    }

    public function getValorAnterior(): ?string
    {
        return $this->valorAnterior;
    }

    public function getValorNuevo(): string
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

    public function setCampo(string $campo): void
    {
        if (trim($campo) === '') {
            throw new InvalidArgumentException("El campo no puede estar vacío");
        }
        $this->campo = $campo;
    }

    public function setValorAnterior(?string $valorAnterior): void
    {
        $this->valorAnterior = $valorAnterior;
    }

    public function setValorNuevo(string $valorNuevo): void
    {
        if (trim($valorNuevo) === '') {
            throw new InvalidArgumentException("El valor nuevo no puede estar vacío");
        }
        $this->valorNuevo = $valorNuevo;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}