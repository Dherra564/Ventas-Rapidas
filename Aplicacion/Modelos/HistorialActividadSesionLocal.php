<?php

class HistorialActividadSesionLocal
{
    public const TIPO_LOGIN = 'Login';
    public const TIPO_ENTRADA_PERFIL = 'EntradaPerfil';

    private const TIPOS_VALIDOS = [self::TIPO_LOGIN, self::TIPO_ENTRADA_PERFIL];
    private const TIPOS_USUARIO_VALIDOS = ['Comerciante', 'Cliente'];

    private int $idHistorialActividadSesionLocal;
    private int $idUsuario;
    private string $tipoUsuario;
    private ?int $idLocal;
    private string $tipo;
    private ?DateTime $fecha;

    public function __construct(
        int $idUsuario,
        string $tipoUsuario,
        string $tipo,
        ?int $idLocal = null,
        int $idHistorialActividadSesionLocal = 0,
        ?DateTime $fecha = null
    ) {
        $this->idHistorialActividadSesionLocal = $idHistorialActividadSesionLocal;
        $this->idLocal = $idLocal;
        $this->fecha = $fecha;

        $this->setIdUsuario($idUsuario);
        $this->setTipoUsuario($tipoUsuario);
        $this->setTipo($tipo);
    }

    public function getIdHistorialActividadSesionLocal(): int
    {
        return $this->idHistorialActividadSesionLocal;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getTipoUsuario(): string
    {
        return $this->tipoUsuario;
    }

    public function getIdLocal(): ?int
    {
        return $this->idLocal;
    }

    public function getTipo(): string
    {
        return $this->tipo;
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
        if (!in_array($tipoUsuario, self::TIPOS_USUARIO_VALIDOS, true)) {
            throw new InvalidArgumentException("Tipo de usuario inválido: $tipoUsuario");
        }
        $this->tipoUsuario = $tipoUsuario;
    }

    public function setTipo(string $tipo): void
    {
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Tipo de actividad inválido: $tipo");
        }
        $this->tipo = $tipo;
    }
}