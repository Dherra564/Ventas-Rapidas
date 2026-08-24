<?php

class Resena
{
    private const PUNTUACION_MINIMA = 1;
    private const PUNTUACION_MAXIMA = 5;

    private int $idResena;
    private int $idCliente;
    private int $idLocal;
    private string $comentario;
    private int $puntuacion;
    private ?DateTime $fechaResena;
    private bool $activo;

    public function __construct(
        int $idCliente,
        int $idLocal,
        string $comentario,
        int $puntuacion,
        bool $activo = true,
        int $idResena = 0,
        ?DateTime $fechaResena = null
    ) {
        $this->idResena = $idResena;
        $this->activo = $activo;
        $this->fechaResena = $fechaResena;

        $this->setIdCliente($idCliente);
        $this->setIdLocal($idLocal);
        $this->setComentario($comentario);
        $this->setPuntuacion($puntuacion);
    }

    public function getIdResena(): int
    {
        return $this->idResena;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function getComentario(): string
    {
        return $this->comentario;
    }

    public function getPuntuacion(): int
    {
        return $this->puntuacion;
    }

    public function getFechaResena(): ?DateTime
    {
        return $this->fechaResena;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdCliente(int $idCliente): void
    {
        if ($idCliente <= 0) {
            throw new InvalidArgumentException("El ID de cliente debe ser mayor a cero");
        }
        $this->idCliente = $idCliente;
    }

    public function setIdLocal(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new InvalidArgumentException("El ID de local debe ser mayor a cero");
        }
        $this->idLocal = $idLocal;
    }

    public function setComentario(string $comentario): void
    {
        if (trim($comentario) === '') {
            throw new InvalidArgumentException("El comentario no puede estar vacío");
        }
        $this->comentario = $comentario;
    }

    public function setPuntuacion(int $puntuacion): void
    {
        if ($puntuacion < self::PUNTUACION_MINIMA || $puntuacion > self::PUNTUACION_MAXIMA) {
            throw new InvalidArgumentException(
                "La puntuación debe estar entre " . self::PUNTUACION_MINIMA . " y " . self::PUNTUACION_MAXIMA
            );
        }
        $this->puntuacion = $puntuacion;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }
}