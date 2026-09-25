<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Pedido.php";
require_once __DIR__ . "/../Modelos/DetallePedido.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/HistorialCampoRepository.php";

class PedidoRepository
{
    use GeneradorId;

    private PDO $conexion;
    private HistorialCampoRepository $historialEstado;
    private HistorialCampoRepository $historialMotivo;
    private HistorialCampoRepository $historialRetiroCodigo;
    private HistorialCampoRepository $historialProductoCantidad;

    private const SELECT_LISTADO = "
        SELECT p.*,
               l.tblocalnombre AS localNombre,
               l.tblocallogo AS localLogo,
               l.tblocaltelefono AS localTelefono,
               u.tbusuarionombrecompleto AS clienteNombre,
               u.tbusuariocorreo AS clienteCorreo
        FROM tbpedido p
        INNER JOIN tblocal l ON l.tblocalid = p.tblocalid
        INNER JOIN tbcliente c ON c.tbclienteid = p.tbclienteid
        INNER JOIN tbusuario u ON u.tbusuarioid = c.tbusuarioid";

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();

        $this->historialEstado = new HistorialCampoRepository(
            "tbpedidoestadohistorico", "tbpedidoestadohistoricoid", "tbpedidoid", $this->conexion
        );
        $this->historialMotivo = new HistorialCampoRepository(
            "tbpedidomotivohistorico", "tbpedidomotivohistoricoid", "tbpedidoid", $this->conexion
        );
        $this->historialRetiroCodigo = new HistorialCampoRepository(
            "tbpedidoretirocodigohistorico", "tbpedidoretirocodigohistoricoid", "tbpedidoid", $this->conexion
        );
        $this->historialProductoCantidad = new HistorialCampoRepository(
            "tbproductocantidadhistorico", "tbproductocantidadhistoricoid", "tbproductoid", $this->conexion
        );
    }

    public function insertar(Pedido $pedido, ?int $idUsuarioAutor = null, ?string $tipoUsuarioAutor = null): int
    {
        if (empty($pedido->getDetalles())) {
            throw new InvalidArgumentException("El pedido no tiene productos");
        }

        $this->conexion->beginTransaction();

        try {

            $sqlStock = "SELECT tbproductocantidad, tbproductonombre
                         FROM tbproducto
                         WHERE tbproductoid = :id AND tbproductoactivo = 1
                         FOR UPDATE";
            $consultaStock = $this->conexion->prepare($sqlStock);

            $cantidadesAnteriores = [];

            foreach ($pedido->getDetalles() as $detalle) {
                $consultaStock->execute([":id" => $detalle->getIdProducto()]);
                $fila = $consultaStock->fetch(PDO::FETCH_ASSOC);

                if (!$fila) {
                    throw new InvalidArgumentException("El producto \"{$detalle->getProductoNombre()}\" ya no está disponible");
                }

                $disponible = (int) $fila["tbproductocantidad"];

                if ($disponible < $detalle->getCantidad()) {
                    throw new InvalidArgumentException(
                        $disponible <= 0
                            ? "\"{$fila['tbproductonombre']}\" se acaba de agotar"
                            : "Solo quedan {$disponible} unidades de \"{$fila['tbproductonombre']}\""
                    );
                }

                $cantidadesAnteriores[$detalle->getIdProducto()] = $disponible;
            }

            $sqlDescontar = "UPDATE tbproducto
                             SET tbproductocantidad = :cantidadNueva
                             WHERE tbproductoid = :id";
            $consultaDescontar = $this->conexion->prepare($sqlDescontar);

            foreach ($pedido->getDetalles() as $detalle) {
                $idProducto = $detalle->getIdProducto();
                $cantidadAnterior = $cantidadesAnteriores[$idProducto];
                $cantidadNueva = $cantidadAnterior - $detalle->getCantidad();

                $consultaDescontar->execute([
                    ":cantidadNueva" => $cantidadNueva,
                    ":id" => $idProducto
                ]);

                $this->historialProductoCantidad->registrar(
                    $idProducto, $cantidadAnterior, $cantidadNueva, $idUsuarioAutor, $tipoUsuarioAutor
                );
            }

            $idPedido = $this->generarSiguienteId($this->conexion, "tbpedido", "tbpedidoid");

            $sqlPedido = "INSERT INTO tbpedido
                          (
                              tbpedidoid,
                              tbclienteid,
                              tblocalid,
                              tbpedidoestado,
                              tbpedidototal,
                              tbpedidoretirocodigo,
                              tbpedidomotivo,
                              tbpedidoregistrofecha,
                              tbpedidoactualizacionfecha,
                              tbpedidoactivo
                          )
                          VALUES
                          (
                              :id,
                              :idCliente,
                              :idLocal,
                              :estado,
                              :total,
                              NULL,
                              NULL,
                              NOW(),
                              NULL,
                              :activo
                          )";

            $this->conexion->prepare($sqlPedido)->execute([
                ":id" => $idPedido,
                ":idCliente" => $pedido->getIdCliente(),
                ":idLocal" => $pedido->getIdLocal(),
                ":estado" => $pedido->getEstado(),
                ":total" => $pedido->getTotal(),
                ":activo" => $pedido->isActivo() ? 1 : 0
            ]);

            $sqlDetalle = "INSERT INTO tbpedidodetalle
                           (
                               tbpedidodetalleid,
                               tbpedidoid,
                               tbproductoid,
                               tbpedidodetalleproductonombre,
                               tbpedidodetallecantidad,
                               tbpedidodetalleprecio,
                               tbpedidodetalledescuentoporcentaje,
                               tbpedidodetallepreciounitario,
                               tbpedidodetallesubtotal,
                               tbpedidodetalleactivo
                           )
                           VALUES
                           (
                               :id,
                               :idPedido,
                               :idProducto,
                               :productoNombre,
                               :cantidad,
                               :precio,
                               :descuentoPorcentaje,
                               :precioUnitario,
                               :subtotal,
                               :activo
                           )";
            $consultaDetalle = $this->conexion->prepare($sqlDetalle);

            foreach ($pedido->getDetalles() as $detalle) {
                $idDetalle = $this->generarSiguienteId($this->conexion, "tbpedidodetalle", "tbpedidodetalleid");

                $consultaDetalle->execute([
                    ":id" => $idDetalle,
                    ":idPedido" => $idPedido,
                    ":idProducto" => $detalle->getIdProducto(),
                    ":productoNombre" => $detalle->getProductoNombre(),
                    ":cantidad" => $detalle->getCantidad(),
                    ":precio" => $detalle->getPrecio(),
                    ":descuentoPorcentaje" => $detalle->getDescuentoPorcentaje(),
                    ":precioUnitario" => $detalle->getPrecioUnitario(),
                    ":subtotal" => $detalle->getSubtotal(),
                    ":activo" => $detalle->isActivo() ? 1 : 0
                ]);
            }

            $this->historialEstado->registrar(
                $idPedido, null, $pedido->getEstado(), $idUsuarioAutor, $tipoUsuarioAutor
            );

            $this->conexion->commit();
            return $idPedido;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    public function cambiarEstado(
        Pedido $pedido,
        string $nuevoEstado,
        ?string $retiroCodigo = null,
        ?string $motivo = null,
        ?int $idUsuarioAutor = null,
        ?string $tipoUsuarioAutor = null
    ): bool {
        $idPedido = $pedido->getIdPedido();
        $estadoAnterior = $pedido->getEstado();

        $campos = [
            "tbpedidoestado = :nuevoEstado",
            "tbpedidoactualizacionfecha = NOW()"
        ];
        $parametros = [
            ":nuevoEstado" => $nuevoEstado,
            ":id" => $idPedido,
            ":estadoAnterior" => $estadoAnterior
        ];

        if ($retiroCodigo !== null) {
            $campos[] = "tbpedidoretirocodigo = :retiroCodigo";
            $parametros[":retiroCodigo"] = $retiroCodigo;
        }

        if ($motivo !== null) {
            $campos[] = "tbpedidomotivo = :motivo";
            $parametros[":motivo"] = $motivo;
        }

        $this->conexion->beginTransaction();

        try {

            $sql = "UPDATE tbpedido
                    SET " . implode(", ", $campos) . "
                    WHERE tbpedidoid = :id
                      AND tbpedidoestado = :estadoAnterior";

            $consulta = $this->conexion->prepare($sql);
            $consulta->execute($parametros);

            if ($consulta->rowCount() === 0) {
                throw new InvalidArgumentException(
                    "El pedido cambió de estado mientras lo revisabas. Actualiza la lista e intenta de nuevo."
                );
            }

            $this->historialEstado->registrar(
                $idPedido, $estadoAnterior, $nuevoEstado, $idUsuarioAutor, $tipoUsuarioAutor
            );

            if ($retiroCodigo !== null) {
                $this->historialRetiroCodigo->registrarSiCambio(
                    $idPedido, $pedido->getRetiroCodigo(), $retiroCodigo, $idUsuarioAutor, $tipoUsuarioAutor
                );
            }

            if ($motivo !== null) {
                $this->historialMotivo->registrarSiCambio(
                    $idPedido, $pedido->getMotivo(), $motivo, $idUsuarioAutor, $tipoUsuarioAutor
                );
            }

            if (Pedido::estadoDevuelveInventario($nuevoEstado)) {
                $this->devolverInventario($idPedido, $idUsuarioAutor, $tipoUsuarioAutor);
            }

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            $this->conexion->rollBack();
            throw $e;
        }
    }

    private function devolverInventario(int $idPedido, ?int $idUsuarioAutor, ?string $tipoUsuarioAutor): void
    {
        $sql = "SELECT d.tbproductoid, d.tbpedidodetallecantidad, p.tbproductocantidad
                FROM tbpedidodetalle d
                INNER JOIN tbproducto p ON p.tbproductoid = d.tbproductoid
                WHERE d.tbpedidoid = :idPedido
                  AND d.tbpedidodetalleactivo = 1
                FOR UPDATE";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idPedido" => $idPedido]);
        $filas = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $sqlActualizar = "UPDATE tbproducto SET tbproductocantidad = :cantidadNueva WHERE tbproductoid = :id";
        $consultaActualizar = $this->conexion->prepare($sqlActualizar);

        foreach ($filas as $fila) {
            $idProducto = (int) $fila["tbproductoid"];
            $cantidadAnterior = (int) $fila["tbproductocantidad"];
            $cantidadNueva = $cantidadAnterior + (int) $fila["tbpedidodetallecantidad"];

            $consultaActualizar->execute([
                ":cantidadNueva" => $cantidadNueva,
                ":id" => $idProducto
            ]);

            $this->historialProductoCantidad->registrar(
                $idProducto, $cantidadAnterior, $cantidadNueva, $idUsuarioAutor, $tipoUsuarioAutor
            );
        }
    }

    public function obtenerPorId(int $idPedido): ?array
    {
        $sql = self::SELECT_LISTADO . " WHERE p.tbpedidoid = :id AND p.tbpedidoactivo = 1";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idPedido]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return $this->mapearListado([$fila])[0];
    }

    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = self::SELECT_LISTADO . "
                WHERE p.tbclienteid = :idCliente
                  AND p.tbpedidoactivo = 1
                ORDER BY p.tbpedidoregistrofecha DESC, p.tbpedidoid DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":idCliente" => $idCliente]);

        return $this->mapearListado($consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerPorLocales(array $idsLocales, ?string $estado = null): array
    {
        $idsLocales = array_values(array_filter(array_map('intval', $idsLocales), fn($id) => $id > 0));

        if (empty($idsLocales)) {
            return [];
        }

        $marcadores = implode(",", array_fill(0, count($idsLocales), "?"));
        $sql = self::SELECT_LISTADO . " WHERE p.tblocalid IN ($marcadores) AND p.tbpedidoactivo = 1";
        $parametros = $idsLocales;

        if ($estado !== null) {
            $sql .= " AND p.tbpedidoestado = ?";
            $parametros[] = $estado;
        }

        $sql .= " ORDER BY (p.tbpedidoestado = 'Pendiente') DESC, p.tbpedidoregistrofecha DESC, p.tbpedidoid DESC";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $this->mapearListado($consulta->fetchAll(PDO::FETCH_ASSOC));
    }

    public function obtenerHistorialEstado(int $idPedido): array
    {
        return $this->historialEstado->obtenerPorEntidad($idPedido);
    }

    public function existeRetiroCodigoActivoEnLocal(int $idLocal, string $retiroCodigo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbpedido
                WHERE tblocalid = :idLocal
                  AND tbpedidoretirocodigo = :retiroCodigo
                  AND tbpedidoestado = :estado";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idLocal" => $idLocal,
            ":retiroCodigo" => $retiroCodigo,
            ":estado" => Pedido::ESTADO_CONFIRMADO
        ]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function productoSeOfreceEnLocal(int $idProducto, int $idLocal): bool
    {
        $sql = "SELECT
                    (SELECT COUNT(*) FROM tbproducto
                     WHERE tbproductoid = :idProducto1 AND tblocalid = :idLocal1)
                  + (SELECT COUNT(*) FROM tbproductolocal
                     WHERE tbproductoid = :idProducto2 AND tblocalid = :idLocal2
                       AND tbproductolocalactivo = 1) AS total";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ":idProducto1" => $idProducto,
            ":idLocal1" => $idLocal,
            ":idProducto2" => $idProducto,
            ":idLocal2" => $idLocal
        ]);
        return (int) $consulta->fetchColumn() > 0;
    }

    private function mapearListado(array $filas): array
    {
        if (empty($filas)) {
            return [];
        }

        $idsPedidos = array_map(fn($fila) => (int) $fila["tbpedidoid"], $filas);
        $detallesPorPedido = $this->obtenerDetallesPorPedidos($idsPedidos);

        $resultado = [];

        foreach ($filas as $fila) {
            $pedido = $this->mapearFila($fila);
            $pedido->setDetalles($detallesPorPedido[$pedido->getIdPedido()] ?? []);

            $resultado[] = [
                "pedido" => $pedido,
                "localNombre" => $fila["localNombre"],
                "localLogo" => $fila["localLogo"],
                "localTelefono" => $fila["localTelefono"],
                "clienteNombre" => $fila["clienteNombre"],
                "clienteCorreo" => $fila["clienteCorreo"]
            ];
        }

        return $resultado;
    }

    private function obtenerDetallesPorPedidos(array $idsPedidos): array
    {
        if (empty($idsPedidos)) {
            return [];
        }

        $marcadores = implode(",", array_fill(0, count($idsPedidos), "?"));
        $sql = "SELECT * FROM tbpedidodetalle
                WHERE tbpedidoid IN ($marcadores)
                  AND tbpedidodetalleactivo = 1
                ORDER BY tbpedidodetalleid";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute(array_values($idsPedidos));

        $agrupados = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $idPedido = (int) $fila["tbpedidoid"];

            $agrupados[$idPedido][] = new DetallePedido(
                (int) $fila["tbproductoid"],
                $fila["tbpedidodetalleproductonombre"],
                (int) $fila["tbpedidodetallecantidad"],
                (float) $fila["tbpedidodetalleprecio"],
                $fila["tbpedidodetalledescuentoporcentaje"] !== null
                    ? (float) $fila["tbpedidodetalledescuentoporcentaje"]
                    : null,
                (float) $fila["tbpedidodetallepreciounitario"],
                (bool) $fila["tbpedidodetalleactivo"],
                $idPedido,
                (int) $fila["tbpedidodetalleid"]
            );
        }

        return $agrupados;
    }

    private function mapearFila(array $fila): Pedido
    {
        return new Pedido(
            (int) $fila["tbclienteid"],
            (int) $fila["tblocalid"],
            $fila["tbpedidoestado"],
            $fila["tbpedidoretirocodigo"],
            $fila["tbpedidomotivo"],
            (bool) $fila["tbpedidoactivo"],
            (int) $fila["tbpedidoid"],
            $fila["tbpedidoregistrofecha"] != null
                ? new DateTime($fila["tbpedidoregistrofecha"])
                : null,
            $fila["tbpedidoactualizacionfecha"] != null
                ? new DateTime($fila["tbpedidoactualizacionfecha"])
                : null,
            (float) $fila["tbpedidototal"]
        );
    }
}