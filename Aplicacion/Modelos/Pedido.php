<?php

require_once __DIR__ . "/DetallePedido.php";

class Pedido
{
    public const ESTADO_PENDIENTE = "Pendiente";
    public const ESTADO_CONFIRMADO = "Confirmado";
    public const ESTADO_RECHAZADO = "Rechazado";
    public const ESTADO_ENTREGADO = "Entregado";
    public const ESTADO_CANCELADO = "Cancelado";

    public const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_CONFIRMADO,
        self::ESTADO_RECHAZADO,
        self::ESTADO_ENTREGADO,
        self::ESTADO_CANCELADO
    ];

    private const TRANSICIONES = [
        self::ESTADO_PENDIENTE => [self::ESTADO_CONFIRMADO, self::ESTADO_RECHAZADO, self::ESTADO_CANCELADO],
        self::ESTADO_CONFIRMADO => [self::ESTADO_ENTREGADO, self::ESTADO_CANCELADO],
        self::ESTADO_RECHAZADO => [],
        self::ESTADO_ENTREGADO => [],
        self::ESTADO_CANCELADO => []
    ];

    private int $idPedido;
    private int $idCliente;
    private int $idLocal;
    private string $estado;
    private ?string $retiroCodigo;
    private ?string $motivo;
    private bool $activo;
    private ?DateTime $registroFecha;
    private ?DateTime $actualizacionFecha;
    private ?float $totalGuardado;
    /** @var DetallePedido[] */
    private array $detalles = [];

    public function __construct(
        int $idCliente,
        int $idLocal,
        string $estado = self::ESTADO_PENDIENTE,
        ?string $retiroCodigo = null,
        ?string $motivo = null,
        bool $activo = true,
        int $idPedido = 0,
        ?DateTime $registroFecha = null,
        ?DateTime $actualizacionFecha = null,
        ?float $totalGuardado = null
    ) {
        $this->idPedido = $idPedido;
        $this->retiroCodigo = $retiroCodigo;
        $this->motivo = $motivo;
        $this->activo = $activo;
        $this->registroFecha = $registroFecha;
        $this->actualizacionFecha = $actualizacionFecha;
        $this->totalGuardado = $totalGuardado;

        $this->setIdCliente($idCliente);
        $this->setIdLocal($idLocal);
        $this->setEstado($estado);
    }

    public function getIdPedido(): int
    {
        return $this->idPedido;
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdLocal(): int
    {
        return $this->idLocal;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getRetiroCodigo(): ?string
    {
        return $this->retiroCodigo;
    }

    public function getMotivo(): ?string
    {
        return $this->motivo;
    }

    public function isActivo(): bool
    {
        return $this->activo;
    }

    public function getRegistroFecha(): ?DateTime
    {
        return $this->registroFecha;
    }

    public function getActualizacionFecha(): ?DateTime
    {
        return $this->actualizacionFecha;
    }

    public function getDetalles(): array
    {
        return $this->detalles;
    }

    public function agregarDetalle(DetallePedido $detalle): void
    {
        $this->detalles[] = $detalle;
    }

    public function setDetalles(array $detalles): void
    {
        $this->detalles = $detalles;
    }

    public function getTotal(): float
    {
        if (empty($this->detalles) && $this->totalGuardado !== null) {
            return $this->totalGuardado;
        }

        $total = 0.0;
        foreach ($this->detalles as $detalle) {
            $total += $detalle->getSubtotal();
        }
        return round($total, 2);
    }

    public function getCantidadArticulos(): int
    {
        $cantidad = 0;
        foreach ($this->detalles as $detalle) {
            $cantidad += $detalle->getCantidad();
        }
        return $cantidad;
    }

    public function setIdCliente(int $idCliente): void
    {
        if ($idCliente <= 0) {
            throw new InvalidArgumentException("El cliente del pedido no es válido");
        }
        $this->idCliente = $idCliente;
    }

    public function setIdLocal(int $idLocal): void
    {
        if ($idLocal <= 0) {
            throw new InvalidArgumentException("El local del pedido no es válido");
        }
        $this->idLocal = $idLocal;
    }

    public function setEstado(string $estado): void
    {
        if (!in_array($estado, self::ESTADOS, true)) {
            throw new InvalidArgumentException("Estado de pedido no válido: {$estado}");
        }
        $this->estado = $estado;
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    public function puedeCambiarA(string $nuevoEstado): bool
    {
        return in_array($nuevoEstado, self::TRANSICIONES[$this->estado] ?? [], true);
    }

    public static function estadoDevuelveInventario(string $estado): bool
    {
        return $estado === self::ESTADO_RECHAZADO || $estado === self::ESTADO_CANCELADO;
    }
}