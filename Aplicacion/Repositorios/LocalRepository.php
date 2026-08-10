<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Local.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/UbicacionRepository.php";

class LocalRepository
{
    private PDO $conexion;
    private UbicacionRepository $ubicacionRepository;

    public function __construct()
    {
        $this->conexion = BaseDatos::obtenerConexion();
        $this->ubicacionRepository = new UbicacionRepository();
    }


    public function insertar(Local $local, Ubicacion $ubicacion): bool
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "INSERT INTO tblocal
                    (
                        tbproveedorid,
                        tblocalnombre,
                        tblocaldescripcion,
                        tblocaltelefono,
                        tblocalcorreo,
                        tblocallogo,
                        tblocalactivo
                    )
                    VALUES
                    (
                        :idProveedor,
                        :nombre,
                        :descripcion,
                        :telefono,
                        :correo,
                        :logo,
                        :activo
                    )";

            $consulta = $this->conexion->prepare($sql);

            $consulta->execute([
                ":idProveedor" => $local->getIdComerciante(),
                ":nombre" => $local->getNombreLocal(),
                ":descripcion" => $local->getDescripcion(),
                ":telefono" => $local->getTelefono(),
                ":correo" => $local->getCorreo(),
                ":logo" => $local->getLogo(),
                ":activo" => $local->isActivo()
            ]);

            $idLocal = (int) $this->conexion->lastInsertId();

            $ubicacion->setIdLocal($idLocal);

            $this->ubicacionRepository->insertar($ubicacion);

            $this->conexion->commit();

            return true;

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
            $locales[] = new Local(
                (int) $fila["tbproveedorid"],
                $fila["tblocalnombre"],
                $fila["tblocaltelefono"],
                $fila["tblocalcorreo"],
                $fila["tblocaldescripcion"],
                $fila["tblocallogo"],
                (bool) $fila["tblocalactivo"],
                (int) $fila["tblocalid"],
                $fila["tblocalfecharegistro"] != null
                ? new DateTime($fila["tblocalfecharegistro"])
                : null
            );
        }

        return $locales;
    }

    public function obtenerPorId(int $idLocal): ?Local
    {
        $sql = "SELECT * FROM tblocal WHERE tblocalid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idLocal]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        return new Local(
            (int) $fila["tbproveedorid"],
            $fila["tblocalnombre"],
            $fila["tblocaltelefono"],
            $fila["tblocalcorreo"],
            $fila["tblocaldescripcion"],
            $fila["tblocallogo"],
            (bool) $fila["tblocalactivo"],
            (int) $fila["tblocalid"],
            $fila["tblocalfecharegistro"] != null
            ? new DateTime($fila["tblocalfecharegistro"])
            : null
        );
    }

    public function obtenerLocalConUbicacion(int $idLocal): ?array
    {
        $sql = "SELECT *
                FROM tblocal l
                INNER JOIN tbubicacion u
                ON l.tblocalid = u.tblocalid
                WHERE l.tblocalid = :id";


        $consulta = $this->conexion->prepare($sql);


        $consulta->execute([
            ":id" => $idLocal
        ]);


        $fila = $consulta->fetch(PDO::FETCH_ASSOC);


        if (!$fila) {
            return null;
        }


        $local = new Local(
            (int) $fila["tbcomercianteid"],
            $fila["tblocalnombre"],
            $fila["tblocaltelefono"],
            $fila["tblocalcorreo"],
            $fila["tblocaldescripcion"],
            $fila["tblocallogo"],
            (bool) $fila["tblocalactivo"],
            (int) $fila["tblocalid"],

            $fila["tblocalfecharegistro"] != null
            ? new DateTime($fila["tblocalfecharegistro"])
            : null
        );


        $ubicacion = new Ubicacion(

            (int) $fila["tblocalid"],
            $fila["tbubicacionprovincia"],
            $fila["tbubicacioncanton"],
            $fila["tbubicaciondistrito"],
            $fila["tbubicaciondireccionexacta"],
            $fila["tbubicacionreferencia"],
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );


        return [
            "local" => $local,
            "ubicacion" => $ubicacion
        ];
    }

    public function actualizar(Local $local, Ubicacion $ubicacion): bool
    {
        try {

            $this->conexion->beginTransaction();


            $sql = "UPDATE tblocal
                    SET
                        tbproveedorid = :idProveedor,
                        tblocalnombre = :nombre,
                        tblocaldescripcion = :descripcion,
                        tblocaltelefono = :telefono,
                        tblocalcorreo = :correo,
                        tblocallogo = :logo,
                        tblocalactivo = :activo
                    WHERE tblocalid = :id";


            $consulta = $this->conexion->prepare($sql);


            $consulta->execute([

                ":idProveedor" => $local->getIdComerciante(),
                ":nombre" => $local->getNombreLocal(),
                ":descripcion" => $local->getDescripcion(),
                ":telefono" => $local->getTelefono(),
                ":correo" => $local->getCorreo(),
                ":logo" => $local->getLogo(),
                ":activo" => $local->isActivo(),
                ":id" => $local->getIdLocal()

            ]);



            $ubicacion->setIdLocal($local->getIdLocal());


            $this->ubicacionRepository->actualizar($ubicacion);



            $this->conexion->commit();


            return true;



        } catch (Exception $e) {


            $this->conexion->rollBack();


            return false;

        }
    }




    public function eliminar(int $idLocal): bool
    {

        try {

            $this->conexion->beginTransaction();



            $sqlLocal = "UPDATE tblocal
                         SET tblocalactivo = 0
                         WHERE tblocalid = :id";


            $consultaLocal = $this->conexion->prepare($sqlLocal);


            $consultaLocal->execute([
                ":id" => $idLocal
            ]);




            $sqlUbicacion = "UPDATE tbubicacion
                             SET tbubicacionactivo = 0
                             WHERE tblocalid = :id";


            $consultaUbicacion = $this->conexion->prepare($sqlUbicacion);


            $consultaUbicacion->execute([
                ":id" => $idLocal
            ]);




            $this->conexion->commit();


            return true;



        } catch (Exception $e) {


            $this->conexion->rollBack();


            return false;

        }

    }

    public function existeNombre(string $nombreLocal): bool
    {
        $sql = "SELECT COUNT(*) FROM tblocal WHERE tblocalnombre = :nombre";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":nombre" => $nombreLocal]);
        return (int) $consulta->fetchColumn() > 0;
    }

}