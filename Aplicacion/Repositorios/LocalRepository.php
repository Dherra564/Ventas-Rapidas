<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Modelos/ComercianteLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
require_once __DIR__ . "/../Comun/ComparadorTexto.php";
require_once __DIR__ . "/UbicacionRepository.php";
require_once __DIR__ . "/ComercianteLocalRepository.php";

class LocalRepository
{
    use GeneradorId, ValidadorReferencia, ComparadorTexto;

    private PDO $conexion;
    private UbicacionRepository $ubicacionRepository;
    private ComercianteLocalRepository $comercianteLocalRepository;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
        $this->ubicacionRepository = new UbicacionRepository($this->conexion);
        $this->comercianteLocalRepository = new ComercianteLocalRepository($this->conexion);
    }

    public function insertar(Local $local, Ubicacion $ubicacion, int $idComerciante): int|false
    {
        try {
            $this->conexion->beginTransaction();

            $this->validarReferencia(
                $this->conexion,
                "tblocaltipo",
                "tblocaltipoid",
                $local->getIdTipoLocal(),
                "El tipo de local con ID {$local->getIdTipoLocal()} no existe"
            );

            $idLocal = $this->generarSiguienteId($this->conexion, "tblocal", "tblocalid");

            $sql = "INSERT INTO tblocal
                    (
                        tblocalid,
                        tblocaltipoid,
                        tblocalnombre,
                        tblocaldescripcion,
                        tblocaltelefono,
                        tblocalcorreo,
                        tblocallogo,
                        tblocalactivo
                    )
                    VALUES
                    (
                        :id,
                        :idTipoLocal,
                        :nombre,
                        :descripcion,
                        :telefono,
                        :correo,
                        :logo,
                        :activo
                    )";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ":id" => $idLocal,
                ":idTipoLocal" => $local->getIdTipoLocal(),
                ":nombre" => $local->getNombreLocal(),
                ":descripcion" => $local->getDescripcion(),
                ":telefono" => $local->getTelefono(),
                ":correo" => $local->getCorreo(),
                ":logo" => $local->getLogo(),
                ":activo" => $local->isActivo()
            ]);

            $ubicacion->setIdLocal($idLocal);
            $this->ubicacionRepository->insertar($ubicacion);

            $comercianteLocal = new ComercianteLocal($idComerciante, $idLocal);
            $this->comercianteLocalRepository->insertar($comercianteLocal);

            $this->conexion->commit();

            return $idLocal;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al insertar local: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tblocal ORDER BY tblocalnombre";

        $consulta = $this->conexion->query($sql);

        $locales = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $locales[] = $this->mapearFila($fila);
        }

        return $locales;
    }

    public function obtenerPorId(int $idLocal): ?Local
    {
        $sql = "SELECT * FROM tblocal WHERE tblocalid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function buscar(
        ?string $nombre = null,
        ?int $idTipoLocal = null,
        ?int $idProvincia = null,
        ?int $idCanton = null,
        ?int $idDistrito = null,
        ?bool $activo = null
    ): array {
        $condiciones = [];
        $parametros = [];
        $join = "";

        if ($idProvincia !== null || $idCanton !== null || $idDistrito !== null) {
            $join = "INNER JOIN tbubicacion u ON l.tblocalid = u.tblocalid";
        }

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "(
                l.tblocalnombre LIKE :nombre
                OR EXISTS (
                    SELECT 1 FROM tbproducto p
                    WHERE p.tblocalid = l.tblocalid
                      AND p.tbproductoactivo = 1
                      AND p.tbproductonombre LIKE :nombreProducto
                )
                OR EXISTS (
                    SELECT 1 FROM tbproductolocal pl
                    INNER JOIN tbproducto p2 ON p2.tbproductoid = pl.tbproductoid
                    WHERE pl.tblocalid = l.tblocalid
                      AND pl.tbproductolocalactivo = 1
                      AND p2.tbproductoactivo = 1
                      AND p2.tbproductonombre LIKE :nombreProductoCompartido
                )
            )";
            $parametros[":nombre"] = "%{$nombre}%";
            $parametros[":nombreProducto"] = "%{$nombre}%";
            $parametros[":nombreProductoCompartido"] = "%{$nombre}%";
        }

        if ($idTipoLocal !== null) {
            $condiciones[] = "l.tblocaltipoid = :idTipoLocal";
            $parametros[":idTipoLocal"] = $idTipoLocal;
        }

        if ($idProvincia !== null) {
            $condiciones[] = "u.tbprovinciaid = :idProvincia";
            $parametros[":idProvincia"] = $idProvincia;
        }

        if ($idCanton !== null) {
            $condiciones[] = "u.tbcantonid = :idCanton";
            $parametros[":idCanton"] = $idCanton;
        }

        if ($idDistrito !== null) {
            $condiciones[] = "u.tbdistritoid = :idDistrito";
            $parametros[":idDistrito"] = $idDistrito;
        }

        if ($activo !== null) {
            $condiciones[] = "l.tblocalactivo = :activo";
            $parametros[":activo"] = $activo;
        }

        $sql = "SELECT l.* FROM tblocal l $join";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY l.tblocalnombre";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        $locales = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $locales[] = $this->mapearFila($fila);
        }

        return $locales;
    }

    public function obtenerLocalConUbicacion(int $idLocal): ?array
    {
        $sql = "SELECT *
                FROM tblocal l
                INNER JOIN tbubicacion u ON l.tblocalid = u.tblocalid
                WHERE l.tblocalid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $local = $this->mapearFila($fila);

        $ubicacion = new Ubicacion(
            (int) $fila["tblocalid"],
            (int) $fila["tbprovinciaid"],
            (int) $fila["tbcantonid"],
            (int) $fila["tbdistritoid"],
            $fila["tbubicaciondireccionexacta"],
            $fila["tbubicaciondereferencia"],
            $fila["tbclienteid"] !== null ? (int) $fila["tbclienteid"] : null,
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );

        return ["local" => $local, "ubicacion" => $ubicacion];
    }

    public function actualizar(Local $local): bool
    {
        $this->validarReferencia(
            $this->conexion,
            "tblocaltipo",
            "tblocaltipoid",
            $local->getIdTipoLocal(),
            "El tipo de local con ID {$local->getIdTipoLocal()} no existe"
        );

        $sql = "UPDATE tblocal
                SET
                    tblocaltipoid = :idTipoLocal,
                    tblocalnombre = :nombre,
                    tblocaldescripcion = :descripcion,
                    tblocaltelefono = :telefono,
                    tblocalcorreo = :correo,
                    tblocallogo = :logo,
                    tblocalactivo = :activo
                WHERE tblocalid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":idTipoLocal" => $local->getIdTipoLocal(),
            ":nombre" => $local->getNombreLocal(),
            ":descripcion" => $local->getDescripcion(),
            ":telefono" => $local->getTelefono(),
            ":correo" => $local->getCorreo(),
            ":logo" => $local->getLogo(),
            ":activo" => $local->isActivo(),
            ":id" => $local->getIdLocal()
        ]);
    }

    public function eliminar(int $idLocal): bool
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE tblocal SET tblocalactivo = 0 WHERE tblocalid = :id";
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([":id" => $idLocal]);

            $sqlUbicacion = "UPDATE tbubicacion SET tbubicacionactivo = 0 WHERE tblocalid = :id";
            $consultaUbicacion = $this->conexion->prepare($sqlUbicacion);
            $consultaUbicacion->execute([":id" => $idLocal]);

            $sqlComercianteLocal = "UPDATE tbcomerciantelocal SET tbcomerciantelocalactivo = 0 WHERE tblocalid = :id";
            $consultaComercianteLocal = $this->conexion->prepare($sqlComercianteLocal);
            $consultaComercianteLocal->execute([":id" => $idLocal]);

            $this->conexion->commit();

            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al eliminar local: " . $e->getMessage());
            return false;
        }
    }

    public function buscarSimilares(string $nombre, ?int $idLocalExcluir = null, float $umbralMinimo = 70.0): array
    {
        $sql = "SELECT
                    l.tblocalid,
                    l.tblocalnombre,
                    lt.tblocaltiponombre AS tipoLocal,
                    (
                        SELECT COUNT(*) FROM tbproducto p
                        WHERE p.tblocalid = l.tblocalid AND p.tbproductoactivo = 1
                    ) AS totalProductos
                FROM tblocal l
                LEFT JOIN tblocaltipo lt ON lt.tblocaltipoid = l.tblocaltipoid
                WHERE l.tblocalactivo = 1";

        $params = [];
        if ($idLocalExcluir !== null) {
            $sql .= " AND l.tblocalid != :idExcluir";
            $params[":idExcluir"] = $idLocalExcluir;
        }

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($params);
        $candidatos = $consulta->fetchAll(PDO::FETCH_ASSOC);

        $candidatos = array_map(fn($fila) => [
            "idLocal" => (int) $fila["tblocalid"],
            "nombre" => $fila["tblocalnombre"],
            "tipoLocal" => $fila["tipoLocal"],
            "totalProductos" => (int) $fila["totalProductos"]
        ], $candidatos);

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

    public function sincronizarActivoPorInactividad(int $dias = 7): int
    {
        $sql = "UPDATE tblocal l
                SET l.tblocalactivo = 0
                WHERE l.tblocalactivo = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM tbhistorialactividadsesionlocal h
                      WHERE h.tblocalid = l.tblocalid
                        AND h.tbhistorialactividadsesionlocalfecha >= (NOW() - INTERVAL :dias DAY)
                  )";

        $consulta = $this->conexion->prepare($sql);
        $consulta->bindValue(":dias", $dias, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->rowCount();
    }

    public function reactivar(int $idLocal): bool
    {
        $sql = "UPDATE tblocal SET tblocalactivo = 1 WHERE tblocalid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([":id" => $idLocal]);
    }

    private function mapearFila(array $fila): Local
    {
        return new Local(
            (int) $fila["tblocaltipoid"],
            $fila["tblocalnombre"],
            $fila["tblocaltelefono"],
            $fila["tblocalcorreo"],
            $fila["tblocaldescripcion"],
            $fila["tblocallogo"],
            (bool) $fila["tblocalactivo"],
            (int) $fila["tblocalid"],
            $fila["tblocalregistrofecha"] != null
            ? new DateTime($fila["tblocalregistrofecha"])
            : null
        );
    }

    public function existeNombre(string $nombreLocal): bool
    {
        $sql = "SELECT COUNT(*) FROM tblocal WHERE tblocalnombre = :nombre";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":nombre" => $nombreLocal]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tblocal WHERE tblocalcorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }
}