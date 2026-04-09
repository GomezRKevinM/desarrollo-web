<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCredentialsException extends \RuntimeException
{

    public static function becauseCredentialsAreInvalid(): self
    {
        return new self('Correo o contraseña incorrectos.');
    }

    public static function becauseUserIsNotActive(): self
    {
        return new self('Tu cuenta no está activa. Contacta al administrador.');
    }

}