<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Producto.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
class ProductoRepository
{
    use GeneradorId, ValidadorReferencia;
    private PDO $conexion;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
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

    /**
     * Busca productos combinando filtros opcionales.
     * Todos los parámetros son opcionales; los que se pasen como null se ignoran.
     *
     * @param string|null $nombre         Coincidencia parcial (LIKE) sobre el nombre
     * @param int|null    $idLocal        Coincidencia exacta sobre el local
     * @param int|null    $idTipoProducto Coincidencia exacta sobre el tipo de producto
     * @param float|null  $precioMinimo   Precio mayor o igual a este valor
     * @param float|null  $precioMaximo   Precio menor o igual a este valor
     * @param bool|null   $activo         Coincidencia exacta sobre el estado activo
     */
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

        return $consulta->execute([
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
    }

    public function eliminar(int $idProducto): bool
    {
        $sql = "UPDATE tbproducto SET tbproductoactivo = 0 WHERE tbproductoid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idProducto]);
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