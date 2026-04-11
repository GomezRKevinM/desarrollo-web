<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificacionFechaException extends \InvalidArgumentException
{
    public static function becauseIsEmpty(): self
    {
        return new self('El valor de la fecha no puede estar vacio');
    }

    public static function becauseFormatIsInvalid(string $value): self
    {
        return new self('El formato de fecha es inválido: "' . $value . '". Se espera Y-m-d H:i:s o Y-m-d.');
    }
}