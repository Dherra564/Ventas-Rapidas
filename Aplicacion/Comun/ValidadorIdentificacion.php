<?php

trait ValidadorIdentificacion
{

    protected function validarIdentificacion(string $tipo, string $numero): void
    {
        $numero = trim($numero);

        switch ($tipo) {
            case 'Cedula':
                if (!preg_match('/^\d{9}$/', $numero)) {
                    throw new InvalidArgumentException(
                        "La cédula física debe tener exactamente 9 dígitos numéricos"
                    );
                }
                break;

            case 'DIMEX':
                if (!preg_match('/^\d{11,12}$/', $numero)) {
                    throw new InvalidArgumentException(
                        "El DIMEX debe tener 11 o 12 dígitos numéricos"
                    );
                }
                break;

            case 'Pasaporte':
                if (!preg_match('/^[A-Za-z0-9]{6,15}$/', $numero)) {
                    throw new InvalidArgumentException(
                        "El pasaporte debe tener entre 6 y 15 caracteres alfanuméricos"
                    );
                }
                break;

            default:
                throw new InvalidArgumentException("Tipo de identificación no reconocido");
        }
    }
}