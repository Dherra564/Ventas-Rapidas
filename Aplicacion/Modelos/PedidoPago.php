<?php

require_once __DIR__ . "/../Comun/ValidarTexto.php";

class PedidoPago
{
    use ValidadorTexto;

    private int $idPedidoPago;
    private int $idPedido;
    private string $numeroSinpe;
    private string $codigo;
    private string $referencia;
    private string $comprobante;
    private string $nombre;
    private string $telefono;
    private float $monto;
    private ?DateTime $registroFecha;
    private ?string $recibo;
    private ?DateTime $reciboFecha;
    private bool $activo;

    public function __construct(
        string $nombre,
        string $telefono,
        string $codigo,
        string $referencia,
        string $comprobante,
        string $numeroSinpe = "00000000",
        float $monto = 0,
        int $idPedido = 0,
        int $idPedidoPago = 0,
        ?DateTime $registroFecha = null,
        ?string $recibo = null,
        ?DateTime $reciboFecha = null,
        bool $activo = true
    ) {
        $this->idPedidoPago = $idPedidoPago;
        $this->idPedido = $idPedido;
        $this->registroFecha = $registroFecha;
        $this->recibo = $recibo;
        $this->reciboFecha = $reciboFecha;
        $this->activo = $activo;

        $this->setNombre($nombre);
        $this->setTelefono($telefono);
        $this->setCodigo($codigo);
        $this->setReferencia($referencia);
        $this->setComprobante($comprobante);
        $this->setNumeroSinpe($numeroSinpe);
        $this->setMonto($monto);
    }

    public function getIdPedidoPago(): int
    {
        return $this->idPedidoPago;
    }

    public function getIdPedido(): int
    {
        return $this->idPedido;
    }

    public function getNumeroSinpe(): string
    {
        return $this->numeroSinpe;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getReferencia(): string
    {
        return $this->referencia;
    }

    public function getComprobante(): string
    {
        return $this->comprobante;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getTelefono(): string
    {
        return $this->telefono;
    }

    public function getMonto(): float
    {
        return $this->monto;
    }

    public function getRegistroFecha(): ?DateTime
    {
        return $this->registroFecha;
    }

    public function getRecibo(): ?string
    {
        return $this->recibo;
    }

    public function getReciboFecha(): ?DateTime
    {
        return $this->reciboFecha;
    }

    public function tieneRecibo(): bool
    {
        return $this->recibo !== null;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function setIdPedido(int $idPedido): void
    {
        $this->idPedido = $idPedido;
    }

    public function setNombre(string $nombre): void
    {
        $nombre = trim($nombre);
        if ($nombre === '') {
            throw new InvalidArgumentException("Escribe tu nombre completo");
        }
        $this->validarSoloLetras($nombre, "El nombre");
        $this->nombre = $nombre;
    }

    public function setTelefono(string $telefono): void
    {
        $telefono = preg_replace('/\D/', '', $telefono);
        if ($telefono === '') {
            throw new InvalidArgumentException("Escribe tu teléfono");
        }
        $this->validarTelefono($telefono);
        $this->telefono = $telefono;
    }

    public function setCodigo(string $codigo): void
    {
        $codigo = strtoupper(trim($codigo));
        if (!preg_match('/^[A-Z0-9]{4,10}$/', $codigo)) {
            throw new InvalidArgumentException("El código del pago no es válido");
        }
        $this->codigo = $codigo;
    }

    public function setReferencia(string $referencia): void
    {
        $referencia = trim($referencia);
        if (!preg_match('/^\d{4}$/', $referencia)) {
            throw new InvalidArgumentException("La referencia debe ser los últimos 4 dígitos del SINPE");
        }
        $this->referencia = $referencia;
    }

    public function setComprobante(string $comprobante): void
    {
        if (trim($comprobante) === '') {
            throw new InvalidArgumentException("Sube el comprobante del SINPE");
        }
        $this->comprobante = $comprobante;
    }

    public function setNumeroSinpe(string $numeroSinpe): void
    {
        if (!preg_match('/^\d{8}$/', $numeroSinpe)) {
            throw new InvalidArgumentException("El número SINPE Móvil del local no es válido");
        }
        $this->numeroSinpe = $numeroSinpe;
    }

    public function setMonto(float $monto): void
    {
        if ($monto < 0) {
            throw new InvalidArgumentException("El monto del pago no es válido");
        }
        $this->monto = round($monto, 2);
    }
}