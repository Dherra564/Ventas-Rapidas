<?php

class HistorialPassword
{
    public const TIPO_CLIENTE = 'Cliente';
    public const TIPO_COMERCIANTE = 'Comerciante';

    private const TIPOS_VALIDOS = [self::TIPO_CLIENTE, self::TIPO_COMERCIANTE];

    private int $idHistorialPassword;
    private int $idUsuario;
    private string $tipoUsuario;
    private ?DateTime $fechaCambio;
    private bool $exitoso;
    private bool $activo;

    public function __construct(
        int $idUsuario,
        string $tipoUsuario,
        bool $exitoso = true,
        bool $activo = true,
        int $idHistorialPassword = 0,
        ?DateTime $fechaCambio = null
    ) {
        $this->idHistorialPassword = $idHistorialPassword;
        $this->exitoso = $exitoso;
        $this->activo = $activo;
        $this->fechaCambio = $fechaCambio;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
    }

    public function getIdHistorialPassword(): int
    {
        return $this->idHistorialPassword;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
    }

    public function getFechaCambio(): ?DateTime
    {
        return $this->fechaCambio;
    }

    public function isExitoso(): bool
    {
        return $this->exitoso;
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

    public function setExitoso(bool $exitoso): void
    {
        $this->exitoso = $exitoso;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}