<?php

class ReseniaProducto
{
    private const PUNTUACION_MINIMA = 1;
    private const PUNTUACION_MAXIMA = 5;

    private int $idReseniaProducto;
    private int $idCliente;
    private int $idProducto;
    private string $comentario;
    private int $puntuacion;
    private ?DateTime $fecha;
    private bool $activo;

    public function __construct(
        int $idCliente,
        int $idProducto,
        string $comentario,
        int $puntuacion,
        bool $activo = true,
        int $idReseniaProducto = 0,
        ?DateTime $fecha = null
    ) {
        $this->idReseniaProducto = $idReseniaProducto;
        $this->activo = $activo;
        $this->fecha = $fecha;

        $this->setIdCliente($idCliente);
        $this->setIdProducto($idProducto);
        $this->setComentario($comentario);
        $this->setPuntuacion($puntuacion);
    }

    public function getIdReseniaProducto(): int
    {
        return $this->idReseniaProducto;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdProducto(): int
    {
        return $this->idProducto;
    }

    public function getComentario(): string
    {
        return $this->comentario;
    }

    public function getPuntuacion(): int
    {
        return $this->puntuacion;
    }

    public function getFecha(): ?DateTime
    {
        return $this->fecha;
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

    public function setIdProducto(int $idProducto): void
    {
        if ($idProducto <= 0) {
            throw new InvalidArgumentException("El ID de producto debe ser mayor a cero");
        }
        $this->idProducto = $idProducto;
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