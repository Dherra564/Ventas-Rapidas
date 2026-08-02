<?php

class Horario
{
    private const DIAS_VALIDOS = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];

    private int $idHorario;
    private int $idLocal;
    private string $diaSemana;
    private string $horaApertura;
    private string $horaCierre;

    public function __construct(
        int $idLocal,
        string $diaSemana,
        string $horaApertura,
        string $horaCierre
        ,
        int $idHorario = 0
    ) {
        $this->idHorario = $idHorario;
        $this->idLocal = $idLocal;
        $this->setDiaSemana($diaSemana);
        $this->setHoraApertura($horaApertura);
        $this->setHoraCierre($horaCierre);
    }

    public function getIdHorario(): int
    {
        return $this->idHorario;
    }
    public function getIdLocal(): int
    {
        return $this->idLocal;
    }
    public function getDiaSemana(): string
    {
        return $this->diaSemana;
    }
    public function getHoraApertura(): string
    {
        return $this->horaApertura;
    }
    public function getHoraCierre(): string
    {
        return $this->horaCierre;
    }

    public function setDiaSemana(string $diaSemana): void
    {
        if (!in_array($diaSemana, self::DIAS_VALIDOS, true)) {
            throw new InvalidArgumentException("Día inválido: $diaSemana");
        }
        $this->diaSemana = $diaSemana;
    }

    public function setHoraApertura(string $horaApertura): void
    {
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $horaApertura)) {
            throw new InvalidArgumentException("Formato de hora inválido: $horaApertura");
        }
        $this->horaApertura = $horaApertura;
    }

    public function setHoraCierre(string $horaCierre): void
    {
        if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $horaCierre)) {
            throw new InvalidArgumentException("Formato de hora inválido: $horaCierre");
        }
        $this->horaCierre = $horaCierre;
    }
}