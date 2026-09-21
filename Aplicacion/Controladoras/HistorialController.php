<?php

require_once __DIR__ . "/../Repositorios/HistorialCampoRepository.php";

class HistorialController
{
    private array $repositorios;

        public function __construct()
    {
        $historialUsuario = [
            'nombre' => new HistorialCampoRepository("tbusuarionombrecompletohistorico", "tbusuarionombrecompletohistoricoid", "tbusuarioid"),
            'correo' => new HistorialCampoRepository("tbusuariocorreohistorico", "tbusuariocorreohistoricoid", "tbusuarioid"),
            'perfilImagen' => new HistorialCampoRepository("tbusuarioperfilimagenhistorico", "tbusuarioperfilimagenhistoricoid", "tbusuarioid"),
            'password' => new HistorialCampoRepository("tbusuariopasswordhistorico", "tbusuariopasswordhistoricoid", "tbusuarioid"),
        ];

        $this->repositorios = [
            'Comerciante' => $historialUsuario,
            'Cliente' => $historialUsuario,
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