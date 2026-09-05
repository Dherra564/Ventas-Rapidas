<?php

class SesionUsuario
{
    public const TIPO_CLIENTE = 'Cliente';
    public const TIPO_COMERCIANTE = 'Comerciante';
    public const TIPO_SUPERADMIN = 'SuperAdmin';
    private const TIPOS_VALIDOS = [self::TIPO_CLIENTE, self::TIPO_COMERCIANTE, self::TIPO_SUPERADMIN];

    private int $idSesion;
    private int $idUsuario;
    private string $tipoUsuario;
    private ?DateTime $fechaInicio;
    private ?DateTime $fechaCierre;
    private bool $activo;

    public function __construct(
        int $idUsuario,
        string $tipoUsuario,
        bool $activo = true,
        int $idSesion = 0,
        ?DateTime $fechaInicio = null,
        ?DateTime $fechaCierre = null
    ) {
        $this->idSesion = $idSesion;
        $this->activo = $activo;
        $this->fechaInicio = $fechaInicio;
        $this->fechaCierre = $fechaCierre;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
    }

    public function getIdSesion(): int
    {
        return $this->idSesion;
    }
    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }
    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
    }
    public function getFechaInicio(): ?DateTime
    {
        return $this->fechaInicio;
    }
    public function getFechaCierre(): ?DateTime
    {
        return $this->fechaCierre;
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

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}