<?php

require_once __DIR__ . "/../../Configuracion/BaseDatos.php";
require_once __DIR__ . "/../Modelos/Cliente.php";
require_once __DIR__ . "/../Comun/GeneradorId.php";

class ClienteRepository
{
    use GeneradorId;

    private PDO $conexion;

    public function __construct(?PDO $conexion = null)
    {
        $this->conexion = $conexion ?? BaseDatos::obtenerConexion();
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
        $sql = "UPDATE tbcliente
                SET
                    tbclientenombrecompleto = :nombre,
                    tbclienteperfilimagen = :perfilImagen,
                    tbclientecorreo = :correo,
                    tbclientepassword = :password,
                    tbclienteactivo = :activo
                WHERE tbclienteid = :id";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ":nombre" => $cliente->getNombreCompleto(),
            ":perfilImagen" => $cliente->getPerfilImagen(),
            ":correo" => $cliente->getCorreo(),
            ":password" => $cliente->getPasswordHash(),
            ":activo" => $cliente->isActivo(),
            ":id" => $cliente->getIdCliente()
        ]);
    }

    public function actualizarPasswordHash(int $idCliente, string $passwordHash): bool
    {
        $sql = "UPDATE tbcliente SET tbclientepassword = :password WHERE tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":password" => $passwordHash, ":id" => $idCliente]);
    }

    public function actualizarPerfilImagen(int $idCliente, ?string $perfilImagen): bool
    {
        $sql = "UPDATE tbcliente SET tbclienteperfilimagen = :perfilImagen WHERE tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":perfilImagen" => $perfilImagen, ":id" => $idCliente]);
    }

    public function eliminar(int $idCliente): bool
    {
        $sql = "UPDATE tbcliente SET tbclienteactivo = 0 WHERE tbclienteid = :id";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([":id" => $idCliente]);
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