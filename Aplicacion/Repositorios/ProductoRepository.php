<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Producto.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
require_once __DIR__ . "/../Comun/ComparadorTexto.php";
require_once __DIR__ . "/../Repositorios/HistorialCampoRepository.php";
class ProductoRepository
{
    use GeneradorId, ValidadorReferencia, ComparadorTexto;
    private PDO $conexion;
    private HistorialCampoRepository $historialPrecio;
    private HistorialCampoRepository $historialDescuento;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
        $this->historialPrecio = new HistorialCampoRepository("tbproductopreciohistorico", "tbproductopreciohistoricoid", "tbproductoid", $this->conexion);
        $this->historialDescuento = new HistorialCampoRepository("tbproductodescuentoporcentajehistorico", "tbproductodescuentoporcentajehistoricoid", "tbproductoid", $this->conexion);
    }

    public function insertar(Producto $producto): int|false
    {
        $this->validarReferencia($this->conexion, "tblocal", "tblocalid", $producto->getIdLocal(), "El local con ID {$producto->getIdLocal()} no existe");
        $this->validarReferencia($this->conexion, "tbproductotipo", "tbproductotipoid", $producto->getIdTipoProducto(), "El tipo de producto con ID {$producto->getIdTipoProducto()} no existe");

        $id = $this->generarSiguienteId($this->conexion, "tbproducto", "tbproductoid");

        $sql = "INSERT INTO tbproducto
                (
                    tbproductoid,
                    tblocalid,
                    tbproductotipoid,
                    tbproductonombre,
                    tbproductodescripcion,
                    tbproductocantidad,
                    tbproductoprecio,
                    tbproductodescuentoporcentaje,
                    tbproductoimagen,
                    tbproductoactivo
                )
                VALUES
                (
                    :id,
                    :idLocal,
                    :idTipoProducto,
                    :nombre,
                    :descripcion,
                    :cantidad,
                    :precio,
                    :porcentajeDescuento,
                    :imagen,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":idLocal" => $producto->getIdLocal(),
            ":idTipoProducto" => $producto->getIdTipoProducto(),
            ":nombre" => $producto->getNombre(),
            ":descripcion" => $producto->getDescripcion(),
            ":cantidad" => $producto->getCantidadDisponible(),
            ":precio" => $producto->getPrecioOriginal(),
            ":porcentajeDescuento" => $producto->getPorcentajeDescuento(),
            ":imagen" => $producto->getImagen(),
            ":activo" => $producto->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbproducto ORDER BY tbproductonombre";

        $consulta = $this->conexion->query($sql);

        $productos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = $this->mapearFila($fila);
        }

        return $productos;
    }

    public function obtenerPorId(int $idProducto): ?Producto
    {
        $sql = "SELECT * FROM tbproducto WHERE tbproductoid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idProducto]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerPorLocal(int $idLocal): array
    {
        $sql = "SELECT * FROM tbproducto
                WHERE tblocalid = :idLocal
                AND tbproductoactivo = 1
                ORDER BY tbproductonombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idLocal" => $idLocal]);

        $productos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = $this->mapearFila($fila);
        }

        return $productos;
    }

    public function buscar(
        ?string $nombre = null,
        ?int $idLocal = null,
        ?int $idTipoProducto = null,
        ?float $precioMinimo = null,
        ?float $precioMaximo = null,
        ?bool $activo = null
    ): array {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "tbproductonombre LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($idLocal !== null) {
            $condiciones[] = "tblocalid = :idLocal";
            $parametros[":idLocal"] = $idLocal;
        }

        if ($idTipoProducto !== null) {
            $condiciones[] = "tbproductotipoid = :idTipoProducto";
            $parametros[":idTipoProducto"] = $idTipoProducto;
        }

        if ($precioMinimo !== null) {
            $condiciones[] = "tbproductoprecio >= :precioMinimo";
            $parametros[":precioMinimo"] = $precioMinimo;
        }

        if ($precioMaximo !== null) {
            $condiciones[] = "tbproductoprecio <= :precioMaximo";
            $parametros[":precioMaximo"] = $precioMaximo;
        }

        if ($activo !== null) {
            $condiciones[] = "tbproductoactivo = :activo";
            $parametros[":activo"] = $activo;
        }

        $sql = "SELECT * FROM tbproducto";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY tbproductonombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        $productos = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $productos[] = $this->mapearFila($fila);
        }

        return $productos;
    }

    public function actualizar(Producto $producto): bool
    {
        $this->validarReferencia(
            $this->conexion,
            "tbproductotipo",
            "tbproductotipoid",
            $producto->getIdTipoProducto(),
            "El tipo de producto con ID {$producto->getIdTipoProducto()} no existe"
        );

        $anterior = $this->obtenerPorId($producto->getIdProducto());

        $sql = "UPDATE tbproducto
            SET
                tbproductotipoid = :idTipoProducto,
                tbproductonombre = :nombre,
                tbproductodescripcion = :descripcion,
                tbproductocantidad = :cantidad,
                tbproductoprecio = :precio,
                tbproductodescuentoporcentaje = :porcentajeDescuento,
                tbproductoimagen = :imagen,
                tbproductoactivo = :activo
            WHERE tbproductoid = :id";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":idTipoProducto" => $producto->getIdTipoProducto(),
            ":nombre" => $producto->getNombre(),
            ":descripcion" => $producto->getDescripcion(),
            ":cantidad" => $producto->getCantidadDisponible(),
            ":precio" => $producto->getPrecioOriginal(),
            ":porcentajeDescuento" => $producto->getPorcentajeDescuento(),
            ":imagen" => $producto->getImagen(),
            ":activo" => $producto->isActivo(),
            ":id" => $producto->getIdProducto()
        ]);

        if ($exito && $anterior !== null) {
            $id = $producto->getIdProducto();
            $this->historialPrecio->registrarSiCambio($id, $anterior->getPrecioOriginal(), $producto->getPrecioOriginal());
            $this->historialDescuento->registrarSiCambio($id, $anterior->getPorcentajeDescuento(), $producto->getPorcentajeDescuento());
        }

        return $exito;
    }

    public function eliminar(int $idProducto): bool
    {
        $sql = "UPDATE tbproducto SET tbproductoactivo = 0 WHERE tbproductoid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idProducto]);
    }

    public function buscarLocalesCercanos(
        string $termino,
        float $latitud,
        float $longitud,
        float $radioKm = 5
    ): array {
        $sql = "SELECT
                    l.tblocalid AS idLocal,
                    l.tblocalnombre AS nombreLocal,
                    l.tblocaltelefono AS telefono,
                    l.tblocallogo AS logo,
                    p.tbproductoid AS idProducto,
                    p.tbproductonombre AS nombreProducto,
                    p.tbproductoprecio AS precio,
                    p.tbproductodescuentoporcentaje AS descuento,
                    ROUND(
                        ST_Distance_Sphere(
                            POINT(u.tbubicacionlongitud, u.tbubicacionlatitud),
                            POINT(:longitud, :latitud)
                        ) / 1000, 2
                    ) AS distanciaKm
                FROM tbproducto p
                INNER JOIN tblocal l ON l.tblocalid = p.tblocalid
                INNER JOIN tbproductotipo tp ON tp.tbproductotipoid = p.tbproductotipoid
                INNER JOIN tbubicacion u ON u.tblocalid = l.tblocalid
                WHERE p.tbproductoactivo = 1
                    AND l.tblocalactivo = 1
                    AND u.tbubicacionlatitud IS NOT NULL
                    AND u.tbubicacionlongitud IS NOT NULL
                    AND (p.tbproductonombre LIKE :terminoProducto OR tp.tbproductotiponombre LIKE :terminoTipo)
                HAVING distanciaKm <= :radioKm
                ORDER BY distanciaKm ASC";

        $consulta = $this->conexion->prepare($sql);

        $terminoLike = '%' . $termino . '%';

        $consulta->bindValue(":longitud", $longitud);
        $consulta->bindValue(":latitud", $latitud);
        $consulta->bindValue(":terminoProducto", $terminoLike);
        $consulta->bindValue(":terminoTipo", $terminoLike);
        $consulta->bindValue(":radioKm", $radioKm);

        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarSimilares(string $nombre, ?int $idProductoExcluir = null, float $umbralMinimo = 70.0): array
    {
        $sql = "SELECT p.tbproductoid, p.tbproductonombre, l.tblocalid, l.tblocalnombre
                FROM tbproducto p
                INNER JOIN tblocal l ON l.tblocalid = p.tblocalid
                WHERE p.tbproductoactivo = 1 AND l.tblocalactivo = 1";

        $params = [];
        if ($idProductoExcluir !== null) {
            $sql .= " AND p.tbproductoid != :idExcluir";
            $params[":idExcluir"] = $idProductoExcluir;
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($params);
        $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $productos = [];
        foreach ($filas as $fila) {
            $idProducto = (int) $fila["tbproductoid"];
            if (!isset($productos[$idProducto])) {
                $productos[$idProducto] = [
                    "idProducto" => $idProducto,
                    "nombre" => $fila["tbproductonombre"],
                    "locales" => []
                ];
            }
            $productos[$idProducto]["locales"][] = [
                "idLocal" => (int) $fila["tblocalid"],
                "nombreLocal" => $fila["tblocalnombre"]
            ];
        }

        if (!empty($productos)) {
            $ids = array_keys($productos);
            $placeholders = implode(",", array_fill(0, count($ids), "?"));
            $sqlCompartidos = "SELECT pl.tbproductoid, l.tblocalid, l.tblocalnombre
                                FROM tbproductolocal pl
                                INNER JOIN tblocal l ON l.tblocalid = pl.tblocalid
                                WHERE pl.tbproductoid IN ($placeholders)
                                  AND pl.tbproductolocalactivo = 1
                                  AND l.tblocalactivo = 1";
            $consultaCompartidos = $this->conexion->prepare($sqlCompartidos);
            $consultaCompartidos->execute($ids);
            while ($fila = $consultaCompartidos->fetch(PDO::FETCH_ASSOC)) {
                $idProducto = (int) $fila["tbproductoid"];
                $productos[$idProducto]["locales"][] = [
                    "idLocal" => (int) $fila["tblocalid"],
                    "nombreLocal" => $fila["tblocalnombre"]
                ];
            }
        }

        $candidatos = array_values($productos);

        $ordenados = $this->ordenarPorSimilitud(
            $candidatos,
            $nombre,
            fn($c) => $c["nombre"],
            $umbralMinimo
        );

        return array_map(
            fn($r) => array_merge(["similitud" => $r["similitud"]], $r["dato"]),
            $ordenados
        );
    }

    private function mapearFila(array $fila): Producto
    {
        return new Producto(
            (int) $fila["tblocalid"],
            (int) $fila["tbproductotipoid"],
            $fila["tbproductonombre"],
            (float) $fila["tbproductoprecio"],
            $fila["tbproductodescuentoporcentaje"] !== null ? (float) $fila["tbproductodescuentoporcentaje"] : null,
            $fila["tbproductodescripcion"],
            (int) $fila["tbproductocantidad"],
            $fila["tbproductoimagen"],
            (bool) $fila["tbproductoactivo"],
            (int) $fila["tbproductoid"],
            $fila["tbproductoregistrofecha"] != null
            ? new DateTime($fila["tbproductoregistrofecha"])
            : null
        );
    }
}