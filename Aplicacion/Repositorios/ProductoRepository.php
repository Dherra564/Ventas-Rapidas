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
    private HistorialCampoRepository $historialCantidad;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
        $this->historialPrecio = new HistorialCampoRepository("tbproductopreciohistorico", "tbproductopreciohistoricoid", "tbproductoid", $this->conexion);
        $this->historialDescuento = new HistorialCampoRepository("tbproductodescuentoporcentajehistorico", "tbproductodescuentoporcentajehistoricoid", "tbproductoid", $this->conexion);
        $this->historialCantidad = new HistorialCampoRepository("tbproductocantidadhistorico", "tbproductocantidadhistoricoid", "tbproductoid", $this->conexion);
    }

    private function resolverIdUsuarioDeComerciante(?int $idComerciante): ?int
    {
        if ($idComerciante === null) {
            return null;
        }

        $sql = "SELECT tbusuarioid FROM tbcomerciante WHERE tbcomercianteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idComerciante]);
        $valor = $consulta->fetchColumn();

        return $valor !== false && $valor !== null ? (int) $valor : null;
    }

    public function insertar(Producto $producto, ?int $idComercianteAutor = null): int|false
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
                    tbproductoactivo,
                    tbproductofechavencimiento
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
                    :activo,
                    :fechaVencimiento
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
            ":activo" => $producto->isActivo(),
            ":fechaVencimiento" => $producto->getFechaVencimiento()?->format('Y-m-d H:i:s')
        ]);

        if ($exito) {
            $idUsuarioAutor = $this->resolverIdUsuarioDeComerciante($idComercianteAutor);
            $this->historialPrecio->registrar($id, null, $producto->getPrecioOriginal(), $idUsuarioAutor, 'Comerciante');
            $this->historialCantidad->registrar($id, null, $producto->getCantidadDisponible(), $idUsuarioAutor, 'Comerciante');
            if ($producto->getPorcentajeDescuento() !== null) {
                $this->historialDescuento->registrar($id, null, $producto->getPorcentajeDescuento(), $idUsuarioAutor, 'Comerciante');
            }
        }

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

    public function actualizar(Producto $producto, ?int $idComercianteAutor = null): bool
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
                tbproductoactivo = :activo,
                tbproductofechavencimiento = :fechaVencimiento
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
            ":fechaVencimiento" => $producto->getFechaVencimiento()?->format('Y-m-d H:i:s'),
            ":id" => $producto->getIdProducto()
        ]);

        if ($exito && $anterior !== null) {
            $id = $producto->getIdProducto();
            $idUsuarioAutor = $this->resolverIdUsuarioDeComerciante($idComercianteAutor);
            $this->historialPrecio->registrarSiCambio($id, $anterior->getPrecioOriginal(), $producto->getPrecioOriginal(), $idUsuarioAutor, 'Comerciante');
            $this->historialDescuento->registrarSiCambio($id, $anterior->getPorcentajeDescuento(), $producto->getPorcentajeDescuento(), $idUsuarioAutor, 'Comerciante');
            $this->historialCantidad->registrarSiCambio($id, $anterior->getCantidadDisponible(), $producto->getCantidadDisponible(), $idUsuarioAutor, 'Comerciante');
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
            : null,
            isset($fila["tbproductofechavencimiento"]) && $fila["tbproductofechavencimiento"] != null
            ? new DateTime($fila["tbproductofechavencimiento"])
            : null
        );
    }

    // Productos más recientes de locales activos, con el nombre del local incluido —
    // para el catálogo de inicio, que no muestra locales individuales sino productos sueltos.
    public function obtenerRecientes(int $limite = 8): array
    {
        $sql = "SELECT p.*, l.tblocalnombre AS nombreLocal, l.tblocallogo AS logoLocal
                FROM tbproducto p
                INNER JOIN tblocal l ON l.tblocalid = p.tblocalid
                WHERE p.tbproductoactivo = 1
                  AND l.tblocalactivo = 1
                ORDER BY p.tbproductoregistrofecha DESC
                LIMIT :limite";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":limite", $limite, PDO::PARAM_INT);
        $consulta->execute();

        $resultados = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $resultados[] = [
                "producto" => $this->mapearFila($fila),
                "nombreLocal" => $fila["nombreLocal"],
                "logoLocal" => $fila["logoLocal"]
            ];
        }

        return $resultados;
    }
}