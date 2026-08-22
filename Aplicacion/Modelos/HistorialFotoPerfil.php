<?php

class HistorialFotoPerfil
{
    public const TIPO_CLIENTE = 'Cliente';
    public const TIPO_COMERCIANTE = 'Comerciante';

    private const TIPOS_VALIDOS = [self::TIPO_CLIENTE, self::TIPO_COMERCIANTE];

    private int $idHistorialFotoPerfil;
    private int $idUsuario;
    private string $tipoUsuario;
    private ?string $rutaAnterior;
    private string $rutaNueva;
    private ?DateTime $fechaCambio;
    private bool $activo;

    public function __construct(
        int $idUsuario,
        string $tipoUsuario,
        string $rutaNueva,
        ?string $rutaAnterior = null,
        bool $activo = true,
        int $idHistorialFotoPerfil = 0,
        ?DateTime $fechaCambio = null
    ) {
        $this->idHistorialFotoPerfil = $idHistorialFotoPerfil;
        $this->activo = $activo;
        $this->fechaCambio = $fechaCambio;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
        $this->setRutaNueva($rutaNueva);
        $this->setRutaAnterior($rutaAnterior);
    }

    public function getIdHistorialFotoPerfil(): int
    {
        return $this->idHistorialFotoPerfil;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
    }

    public function getRutaAnterior(): ?string
    {
        return $this->rutaAnterior;
    }

    public function getRutaNueva(): string
    {
        return $this->rutaNueva;
    }

    public function getFechaCambio(): ?DateTime
    {
        return $this->fechaCambio;
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

    public function setRutaNueva(string $rutaNueva): void
    {
        if (trim($rutaNueva) === '') {
            throw new InvalidArgumentException("La ruta de la nueva foto no puede estar vacía");
        }
        $this->rutaNueva = $rutaNueva;
    }

    public function setRutaAnterior(?string $rutaAnterior): void
    {
        if ($rutaAnterior !== null && trim($rutaAnterior) === '') {
            throw new InvalidArgumentException("La ruta anterior no puede estar vacía");
        }
        $this->rutaAnterior = $rutaAnterior;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}