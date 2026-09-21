<?php

require_once __DIR__ . "/../Comun/ValidarTexto.php";
require_once __DIR__ . "/Usuario.php";

class Comerciante
{
    use ValidadorTexto;

    private int $idComerciante;
    private int $idUsuario;
    private string $alias;
    private ?DateTime $fechaRegistro;
    private bool $activo;
    private ?Usuario $usuario;

    public function __construct(
        int $idUsuario,
        string $alias,
        bool $activo = true,
        int $idComerciante = 0,
        ?DateTime $fechaRegistro = null,
        ?Usuario $usuario = null
    ) {
        $this->idComerciante = $idComerciante;
        $this->idUsuario = $idUsuario;
        $this->activo = $activo;
        $this->fechaRegistro = $fechaRegistro;
        $this->usuario = $usuario;

        $this->setAlias($alias);
    }

    public function getIdComerciante(): int { return $this->idComerciante; }
    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function getAlias(): string { return $this->alias; }
    public function getFechaRegistro(): ?DateTime { return $this->fechaRegistro; }
    public function isActivo(): bool { return $this->activo; }

    // Los datos personales viven en Usuario. Estos métodos se conservan
    // para que las APIs/vistas existentes sigan funcionando.
    public function getNombreCompleto(): string { return $this->usuario()->getNombreCompleto(); }
    public function getCedula(): string { return $this->usuario()->getIdentificacion(); }
    public function getNumeroIdentificacion(): string { return $this->usuario()->getIdentificacion(); }
    public function getCorreo(): string { return $this->usuario()->getCorreo(); }
    public function getPasswordHash(): string { return $this->usuario()->getPasswordHash(); }
    public function getPerfilImagen(): ?string { return $this->usuario()->getPerfilImagen(); }
    public function getFotoPerfil(): ?string { return $this->usuario()->getPerfilImagen(); }

    public function setAlias(string $alias): void
    {
        if (trim($alias) === '') {
            throw new InvalidArgumentException("El alias no puede estar vacío");
        }
        $this->validarSoloLetras($alias, "El alias");
        $this->alias = $alias;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    private function usuario(): Usuario
    {
        if ($this->usuario === null) {
            throw new LogicException("El comerciante no tiene cargados los datos de usuario");
        }
        return $this->usuario;
    }
}