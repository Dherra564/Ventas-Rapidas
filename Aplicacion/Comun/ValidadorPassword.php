<?php

trait ValidadorPassword
{
    private const LONGITUD_MINIMA = 8;
    private const PATRON_PERMITIDO = '/^[A-Za-z0-9!@#$%^&*()_\-+=\[\]{};:,.<>?]+$/';

    protected function validarFormatoPassword(string $password): void
    {
        if (strlen($password) < self::LONGITUD_MINIMA) {
            throw new InvalidArgumentException(
                "La contraseña debe tener al menos " . self::LONGITUD_MINIMA . " caracteres"
            );
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new InvalidArgumentException(
                "La contraseña debe tener al menos una letra mayúscula"
            );
        }

        if (!preg_match(self::PATRON_PERMITIDO, $password)) {
            throw new InvalidArgumentException(
                "La contraseña contiene símbolos no permitidos"
            );
        }
    }

    protected function validarPasswordDistinta(string $passwordNueva, string $passwordHashActual): void
    {
        if (password_verify($passwordNueva, $passwordHashActual)) {
            throw new InvalidArgumentException(
                "La nueva contraseña no puede ser igual a la actual"
            );
        }
    }
}