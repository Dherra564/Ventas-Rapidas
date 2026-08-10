<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Modelos/ComercianteLocal.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/UbicacionRepository.php";
require_once __DIR__ . "/ComercianteLocalRepository.php";
require_once __DIR__ . "/../Comun/ValidadorReferencia.php";
class LocalRepository
{
    use GeneradorId, ValidadorReferencia;

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
                        tblocalproductosaofrecer,
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
                        :productos,
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
                ":productos" => $local->getProductosAOfrecer(),
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

    /**
     * Busca locales combinando filtros opcionales.
     * Todos los parámetros son opcionales; los que se pasen como null se ignoran.
     *
     * @param string|null $nombre      Coincidencia parcial (LIKE) sobre el nombre
     * @param int|null    $idTipoLocal Coincidencia exacta sobre el tipo de local
     * @param bool|null   $activo      Coincidencia exacta sobre el estado activo
     */
    public function buscar(?string $nombre = null, ?int $idTipoLocal = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "tblocalnombre LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($idTipoLocal !== null) {
            $condiciones[] = "tblocaltipoid = :idTipoLocal";
            $parametros[":idTipoLocal"] = $idTipoLocal;
        }

        if ($activo !== null) {
            $condiciones[] = "tblocalactivo = :activo";
            $parametros[":activo"] = $activo;
        }

        $sql = "SELECT * FROM tblocal";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY tblocalnombre";

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
                    tblocalproductosaofrecer = :productos,
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
            ":productos" => $local->getProductosAOfrecer(),
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

    private function mapearFila(array $fila): Local
    {
        return new Local(
            (int) $fila["tblocaltipoid"],
            $fila["tblocalnombre"],
            $fila["tblocaltelefono"],
            $fila["tblocalcorreo"],
            $fila["tblocaldescripcion"],
            $fila["tblocalproductosaofrecer"],
            $fila["tblocallogo"],
            (bool) $fila["tblocalactivo"],
            (int) $fila["tblocalid"],
            $fila["tblocalfecharegistroportal"] != null
            ? new DateTime($fila["tblocalfecharegistroportal"])
            : null
        );
    }
}