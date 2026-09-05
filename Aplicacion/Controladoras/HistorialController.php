<?php

require_once __DIR__ . "/../Repositorios/HistorialCampoRepository.php";

class HistorialController
{
    private array $repositorios;

    public function __construct()
    {
        $this->repositorios = [
            'Comerciante' => [
                'nombre' => new HistorialCampoRepository("tbcomerciantenombrehistorico", "tbcomerciantenombrehistoricoid", "tbcomercianteid"),
                'correo' => new HistorialCampoRepository("tbcomerciantecorreohistorico", "tbcomerciantecorreohistoricoid", "tbcomercianteid"),
                'perfilImagen' => new HistorialCampoRepository("tbcomercianteperfilimagenhistorico", "tbcomercianteperfilimagenhistoricoid", "tbcomercianteid"),
                'password' => new HistorialCampoRepository("tbcomerciantepasswordhistorico", "tbcomerciantepasswordhistoricoid", "tbcomercianteid"),
            ],
            'Cliente' => [
                'nombre' => new HistorialCampoRepository("tbclientenombrecompletohistorico", "tbclientenombrecompletohistoricoid", "tbclienteid"),
                'correo' => new HistorialCampoRepository("tbclientecorreohistorico", "tbclientecorreohistoricoid", "tbclienteid"),
                'perfilImagen' => new HistorialCampoRepository("tbclienteperfilimagenhistorico", "tbclienteperfilimagenhistoricoid", "tbclienteid"),
                'password' => new HistorialCampoRepository("tbclientepasswordhistorico", "tbclientepasswordhistoricoid", "tbclienteid"),
            ],
            'Local' => [
                'nombre' => new HistorialCampoRepository("tblocalnombrehistorico", "tblocalnombrehistoricoid", "tblocalid"),
                'telefono' => new HistorialCampoRepository("tblocaltelefonohistorico", "tblocaltelefonohistoricoid", "tblocalid"),
                'logo' => new HistorialCampoRepository("tblocallogohistorico", "tblocallogohistoricoid", "tblocalid"),
            ],
            'Ubicacion' => [
                'provincia' => new HistorialCampoRepository("tbubicacionprovinciahistorico", "tbubicacionprovinciahistoricoid", "tbubicacionid"),
                'canton' => new HistorialCampoRepository("tbubicacioncantonhistorico", "tbubicacioncantonhistoricoid", "tbubicacionid"),
                'distrito' => new HistorialCampoRepository("tbubicaciondistritohistorico", "tbubicaciondistritohistoricoid", "tbubicacionid"),
                'direccionExacta' => new HistorialCampoRepository("tbubicaciondireccionexactahistorico", "tbubicaciondireccionexactahistoricoid", "tbubicacionid"),
            ],
            'Producto' => [
                'precio' => new HistorialCampoRepository("tbproductopreciohistorico", "tbproductopreciohistoricoid", "tbproductoid"),
                'descuento' => new HistorialCampoRepository("tbproductodescuentoporcentajehistorico", "tbproductodescuentoporcentajehistoricoid", "tbproductoid"),
            ],
        ];
    }

    public function listarHistorial(string $entidad, string $campo, int $idEntidad): array
    {
        if (!isset($this->repositorios[$entidad][$campo])) {
            throw new InvalidArgumentException("No existe historial para {$entidad}.{$campo}");
        }

        return $this->repositorios[$entidad][$campo]->obtenerPorEntidad($idEntidad);
    }

    public function listarPasswords(int $idUsuario, string $tipoUsuario): array
    {
        return $this->listarHistorial($tipoUsuario, 'password', $idUsuario);
    }

    public function listarFotos(int $idUsuario, string $tipoUsuario): array
    {
        return $this->listarHistorial($tipoUsuario, 'perfilImagen', $idUsuario);
    }
}