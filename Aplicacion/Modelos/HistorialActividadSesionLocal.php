<?php

class HistorialActividadSesionLocal
{
    public const TIPO_REGISTRO = 'Registro';
    public const TIPO_EDICION = 'Edicion';
    public const TIPO_PRODUCTO_AGREGADO = 'ProductoAgregado';
    public const TIPO_PRODUCTO_EDITADO = 'ProductoEditado';
    public const TIPO_VISTA = 'Vista';
    public const TIPO_ENTRADA_PERFIL = 'EntradaPerfil';

    private const TIPOS_VALIDOS = [
        self::TIPO_REGISTRO,
        self::TIPO_EDICION,
        self::TIPO_PRODUCTO_AGREGADO,
        self::TIPO_PRODUCTO_EDITADO,
        self::TIPO_VISTA,
        self::TIPO_ENTRADA_PERFIL
    ];

    private int $idHistorialActividadSesionLocal;
    private int $idLocal;
    private string $tipo;
    private ?DateTime $fecha;

    public function __construct(
        int $idLocal,
        string $tipo,
        int $idHistorialActividadSesionLocal = 0,
        ?DateTime $fecha = null
    ) {
        $this->idHistorialActividadSesionLocal = $idHistorialActividadSesionLocal;
        $this->fecha = $fecha;

        $this->setIdLocal($idLocal);
        $this->setTipo($tipo);
    }

    public function getIdHistorialActividadSesionLocal(): int
    {
        return $this->idHistorialActividadSesionLocal;
    }

    public function getIdLocal(): int
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

    public function setIdLocal(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new InvalidArgumentException("El ID de local debe ser mayor a cero");
        }
        $this->idLocal = $idLocal;
    }

    public function setTipo(string $tipo): void
    {
        if (!in_array($tipo, self::TIPOS_VALIDOS, true)) {
            throw new InvalidArgumentException("Tipo de actividad inválido: $tipo");
        }
        $this->tipo = $tipo;
    }
}