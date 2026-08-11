<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/UbicacionRepository.php";

class ClienteRepository
{
    use GeneradorId;

    private PDO $conexion;
    private UbicacionRepository $ubicacionRepository;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->ubicacionRepository = new UbicacionRepository($this->conexion);
    }

    /**
     * Inserta el cliente junto con su ubicación, en una sola transacción.
     */
    public function insertarConUbicacion(Cliente $cliente, Ubicacion $ubicacion): int|false
    {
        try {
            $this->conexion->beginTransaction();

            $idCliente = $this->insertarClienteSinTransaccion($cliente);

            if ($idCliente === false) {
                throw new Exception("No se pudo registrar el cliente");
            }

            $ubicacion->setIdCliente($idCliente);

            if (!$ubicacion->tieneDuenoValido()) {
                throw new InvalidArgumentException("La ubicación del cliente no quedó asociada correctamente");
            }

            $this->ubicacionRepository->insertar($ubicacion);

            $this->conexion->commit();

            return $idCliente;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al insertar cliente: " . $e->getMessage());
            return false;
        }
    }

    private function insertarClienteSinTransaccion(Cliente $cliente): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbcliente", "tbclienteid");

        $sql = "INSERT INTO tbcliente
                (
                    tbclienteid,
                    tbclientenombrecompleto,
                    tbclientenumerodeidentificacion,
                    tbclienteimagenperfil,
                    tbclientecorreo,
                    tbclientepassword,
                    tbclienteactivo
                )
                VALUES
                (
                    :id,
                    :nombre,
                    :numeroIdentificacion,
                    :fotoPerfil,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":nombre" => $cliente->getNombreCompleto(),
            ":numeroIdentificacion" => $cliente->getNumeroIdentificacion(),
            ":fotoPerfil" => $cliente->getFotoPerfil(),
            ":correo" => $cliente->getCorreo(),
            ":password" => $cliente->getPasswordHash(),
            ":activo" => $cliente->isActivo()
        ]);

        return $exito ? $id : false;
    }

    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM tbcliente ORDER BY tbclientenombrecompleto";

        $consulta = $this->conexion->query($sql);

        return $this->mapearFilas($consulta);
    }

    public function obtenerPorId(int $idCliente): ?Cliente
    {
        $sql = "SELECT * FROM tbcliente WHERE tbclienteid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idCliente]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }

    public function buscar(?string $nombre = null, ?bool $activo = null): array
    {
        $condiciones = [];
        $parametros = [];

        if ($nombre !== null && $nombre !== "") {
            $condiciones[] = "tbclientenombrecompleto LIKE :nombre";
            $parametros[":nombre"] = "%{$nombre}%";
        }

        if ($activo !== null) {
            $condiciones[] = "tbclienteactivo = :activo";
            $parametros[":activo"] = $activo;
        }

        $sql = "SELECT * FROM tbcliente";

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY tbclientenombrecompleto";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute($parametros);

        return $this->mapearFilas($consulta);
    }

    public function actualizar(Cliente $cliente): bool
    {
        $sql = "UPDATE tbcliente
                SET
                    tbclientenombrecompleto = :nombre,
                    tbclienteimagenperfil = :fotoPerfil,
                    tbclientecorreo = :correo,
                    tbclientepassword = :password,
                    tbclienteactivo = :activo
                WHERE tbclienteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $cliente->getNombreCompleto(),
            ":fotoPerfil" => $cliente->getFotoPerfil(),
            ":correo" => $cliente->getCorreo(),
            ":password" => $cliente->getPasswordHash(),
            ":activo" => $cliente->isActivo(),
            ":id" => $cliente->getIdCliente()
        ]);
    }

    public function eliminar(int $idCliente): bool
    {
        try {
            $this->conexion->beginTransaction();

            $sql = "UPDATE tbcliente SET tbclienteactivo = 0 WHERE tbclienteid = :id";
            $consulta = $this->conexion->prepare($sql);
            $consulta->execute([":id" => $idCliente]);

            $sqlUbicacion = "UPDATE tbubicacion SET tbubicacionactivo = 0 WHERE tbubicacionidcliente = :id";
            $consultaUbicacion = $this->conexion->prepare($sqlUbicacion);
            $consultaUbicacion->execute([":id" => $idCliente]);

            $this->conexion->commit();

            return true;

        } catch (Exception $e) {
            $this->conexion->rollBack();
            error_log("Error al eliminar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerClienteConUbicacion(int $idCliente): ?array
    {
        $sql = "SELECT *
                FROM tbcliente c
                INNER JOIN tbubicacion u ON c.tbclienteid = u.tbubicacionidcliente
                WHERE c.tbclienteid = :id";

        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":id" => $idCliente]);

        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        if (!$fila) {
            return null;
        }

        $cliente = $this->mapearFila($fila);

        $ubicacion = new Ubicacion(
            (int) $fila["tbprovinciaid"],
            (int) $fila["tbcantonid"],
            (int) $fila["tbdistritoid"],
            $fila["tbubicaciondireccionexacta"],
            (int) $fila["tblocalid"],
            (int) $fila["tbubicacionidcliente"],
            $fila["tbubicaciondereferencia"],
            (bool) $fila["tbubicacionactivo"],
            (int) $fila["tbubicacionid"]
        );

        return ["cliente" => $cliente, "ubicacion" => $ubicacion];
    }

    private function mapearFilas(PDOStatement $consulta): array
    {
        $clientes = [];

        while ($fila = $consulta->fetch(PDO::FETCH_ASSOC)) {
            $clientes[] = $this->mapearFila($fila);
        }

        return $clientes;
    }

    private function mapearFila(array $fila): Cliente
    {
        return new Cliente(
            $fila["tbclientenombrecompleto"],
            $fila["tbclientenumerodeidentificacion"],
            $fila["tbclientecorreo"],
            $fila["tbclientepassword"],
            $fila["tbclienteimagenperfil"] ?? '',
            (bool) $fila["tbclienteactivo"],
            (int) $fila["tbclienteid"]
        );
    }

    public function existeIdentificacion(string $numeroIdentificacion): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcliente WHERE tbclientenumerodeidentificacion = :numeroIdentificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":numeroIdentificacion" => $numeroIdentificacion]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcliente WHERE tbclientecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function obtenerPorIdentificacion(string $numeroIdentificacion): ?Cliente
    {
        $sql = "SELECT * FROM tbcliente WHERE tbclientenumerodeidentificacion = :numeroIdentificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":numeroIdentificacion" => $numeroIdentificacion]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);

        return $fila ? $this->mapearFila($fila) : null;
    }
}