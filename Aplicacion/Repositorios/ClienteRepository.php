<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Modelos/Ubicacion.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";
require_once __DIR__ . "/UbicacionRepository.php";
require_once __DIR__ . "/HistorialCampoRepository.php";

class ClienteRepository
{
    use GeneradorId;

    private PDO $conexion;
    private UbicacionRepository $ubicacionRepository;
    private HistorialCampoRepository $historialNombre;
    private HistorialCampoRepository $historialCorreo;
    private HistorialCampoRepository $historialPerfilImagen;
    private HistorialCampoRepository $historialPassword;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
        $this->ubicacionRepository = new UbicacionRepository($this->conexion);
        $this->historialNombre = new HistorialCampoRepository("tbclientenombrecompletohistorico", "tbclientenombrecompletohistoricoid", "tbclienteid", $this->conexion);
        $this->historialCorreo = new HistorialCampoRepository("tbclientecorreohistorico", "tbclientecorreohistoricoid", "tbclienteid", $this->conexion);
        $this->historialPerfilImagen = new HistorialCampoRepository("tbclienteperfilimagenhistorico", "tbclienteperfilimagenhistoricoid", "tbclienteid", $this->conexion);
        $this->historialPassword = new HistorialCampoRepository("tbclientepasswordhistorico", "tbclientepasswordhistoricoid", "tbclienteid", $this->conexion);
    }

    public function insertarConUbicacion(Cliente $cliente, Ubicacion $ubicacion): int|false
    {
        try {
            $this->conexion->beginTransaction();

            $idCliente = $this->insertar($cliente);
            if ($idCliente === false) {
                throw new Exception("No se pudo registrar el cliente");
            }

            $ubicacion->setIdCliente($idCliente);
            $ubicacion->setIdLocal(null);

            if (!$ubicacion->tieneDuenoValido()) {
                throw new InvalidArgumentException("La ubicación del cliente no quedó asociada correctamente");
            }

            $idUbicacion = $this->ubicacionRepository->insertar($ubicacion);
            if ($idUbicacion === false) {
                throw new Exception("No se pudo registrar la ubicación del cliente");
            }

            $this->conexion->commit();
            return $idCliente;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            error_log("Error al insertar cliente: " . $e->getMessage());
            return false;
        }
    }

    public function insertar(Cliente $cliente): int|false
    {
        $id = $this->generarSiguienteId($this->conexion, "tbcliente", "tbclienteid");

        $sql = "INSERT INTO tbcliente
                (
                    tbclienteid,
                    tbclienteidentificacionnumero,
                    tbclientenombrecompleto,
                    tbclienteperfilimagen,
                    tbclientecorreo,
                    tbclientepassword,
                    tbclienteactivo
                )
                VALUES
                (
                    :id,
                    :identificacion,
                    :nombre,
                    :perfilImagen,
                    :correo,
                    :password,
                    :activo
                )";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":id" => $id,
            ":identificacion" => $cliente->getIdentificacion(),
            ":nombre" => $cliente->getNombreCompleto(),
            ":perfilImagen" => $cliente->getPerfilImagen(),
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
        $anterior = $this->obtenerPorId($cliente->getIdCliente());

        $sql = "UPDATE tbcliente
                SET
                    tbclientenombrecompleto = :nombre,
                    tbclienteperfilimagen = :perfilImagen,
                    tbclientecorreo = :correo,
                    tbclientepassword = :password,
                    tbclienteactivo = :activo
                WHERE tbclienteid = :id";

        $consulta = $this->conexion->prepare($sql);

        $exito = $consulta->execute([
            ":nombre" => $cliente->getNombreCompleto(),
            ":perfilImagen" => $cliente->getPerfilImagen(),
            ":correo" => $cliente->getCorreo(),
            ":password" => $cliente->getPasswordHash(),
            ":activo" => $cliente->isActivo(),
            ":id" => $cliente->getIdCliente()
        ]);

        if ($exito && $anterior !== null) {
            $id = $cliente->getIdCliente();
            $this->historialNombre->registrarSiCambio($id, $anterior->getNombreCompleto(), $cliente->getNombreCompleto());
            $this->historialCorreo->registrarSiCambio($id, $anterior->getCorreo(), $cliente->getCorreo());
            $this->historialPerfilImagen->registrarSiCambio($id, $anterior->getPerfilImagen(), $cliente->getPerfilImagen());
        }

        return $exito;
    }

    public function actualizarPasswordHash(int $idCliente, string $passwordHash): bool
    {
        $anterior = $this->obtenerPorId($idCliente);

        $sql = "UPDATE tbcliente SET tbclientepassword = :password WHERE tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":password" => $passwordHash, ":id" => $idCliente]);

        if ($exito && $anterior !== null) {
            $this->historialPassword->registrar($idCliente, $anterior->getPasswordHash(), $passwordHash);
        }

        return $exito;
    }

    public function actualizarPerfilImagen(int $idCliente, ?string $perfilImagen): bool
    {
        $anterior = $this->obtenerPorId($idCliente);

        $sql = "UPDATE tbcliente SET tbclienteperfilimagen = :perfilImagen WHERE tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        $exito = $consulta->execute([":perfilImagen" => $perfilImagen, ":id" => $idCliente]);

        if ($exito && $anterior !== null) {
            $this->historialPerfilImagen->registrarSiCambio($idCliente, $anterior->getPerfilImagen(), $perfilImagen);
        }

        return $exito;
    }

    public function obtenerUltimosHashesPassword(int $idCliente, int $cantidad = 2): array
    {
        return $this->historialPassword->obtenerUltimosValores($idCliente, $cantidad);
    }

    public function activar(int $idCliente): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("UPDATE tbcliente SET tbclienteactivo = 1 WHERE tbclienteid = :id");
            $consulta->execute([":id" => $idCliente]);

            $consultaUbicacion = $this->conexion->prepare("UPDATE tbubicacion SET tbubicacionactivo = 1 WHERE tbclienteid = :id");
            $consultaUbicacion->execute([":id" => $idCliente]);

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    public function eliminar(int $idCliente): bool
    {
        try {
            $this->conexion->beginTransaction();

            $consulta = $this->conexion->prepare("UPDATE tbcliente SET tbclienteactivo = 0 WHERE tbclienteid = :id");
            $consulta->execute([":id" => $idCliente]);

            $consultaUbicacion = $this->conexion->prepare("UPDATE tbubicacion SET tbubicacionactivo = 0 WHERE tbclienteid = :id");
            $consultaUbicacion->execute([":id" => $idCliente]);

            $this->conexion->commit();
            return true;
        } catch (Throwable $e) {
            if ($this->conexion->inTransaction()) {
                $this->conexion->rollBack();
            }
            return false;
        }
    }

    public function existeIdentificacion(string $identificacion): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcliente WHERE tbclienteidentificacionnumero = :identificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":identificacion" => $identificacion]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function existeCorreo(string $correo): bool
    {
        $sql = "SELECT COUNT(*) FROM tbcliente WHERE tbclientecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        return (int) $consulta->fetchColumn() > 0;
    }

    public function obtenerPorIdentificacion(string $identificacion): ?Cliente
    {
        $sql = "SELECT * FROM tbcliente WHERE tbclienteidentificacionnumero = :identificacion";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":identificacion" => $identificacion]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
    }

    public function obtenerClienteConUbicacion(int $idCliente): ?array
    {
        $cliente = $this->obtenerPorId($idCliente);
        if ($cliente === null) {
            return null;
        }

        $ubicacion = $this->ubicacionRepository->obtenerPorCliente($idCliente);
        if ($ubicacion === null) {
            return null;
        }

        return ["cliente" => $cliente, "ubicacion" => $ubicacion];
    }

    public function obtenerPorCorreo(string $correo): ?Cliente
    {
        $sql = "SELECT * FROM tbcliente WHERE tbclientecorreo = :correo";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([":correo" => $correo]);
        $fila = $consulta->fetch(PDO::FETCH_ASSOC);
        return $fila ? $this->mapearFila($fila) : null;
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
            $fila["tbclienteidentificacionnumero"],
            $fila["tbclientecorreo"],
            $fila["tbclientepassword"],
            $fila["tbclienteperfilimagen"],
            (bool) $fila["tbclienteactivo"],
            (int) $fila["tbclienteid"]
        );
    }
}