<?php

trait ValidadorTexto
{
    private const PATRON_SOLO_LETRAS = '/^[A-Za-zÁÉÍÓÚÑáéíóúñÜü\' -]+$/';
    private const PATRON_TELEFONO = '/^\d{8}$/';

    protected function validarSoloLetras(string $valor, string $nombreCampo): void
    {
        if (!preg_match(self::PATRON_SOLO_LETRAS, trim($valor))) {
            throw new InvalidArgumentException(
                "$nombreCampo solo puede contener letras y espacios"
            );
        }
    }

    protected function validarTelefono(string $valor): void
    {
        if (!preg_match(self::PATRON_TELEFONO, trim($valor))) {
            throw new InvalidArgumentException(
                "El teléfono debe tener exactamente 8 dígitos numéricos"
            );
        }
    }
}