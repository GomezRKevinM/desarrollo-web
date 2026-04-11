<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationNotaException extends \InvalidArgumentException
{
    public static function becauseIsEmpty(): self
    {
        return new self('el valor del periodo no puede estar vacio');
    }

    public static function becauseLengthIsTooLong(int $max): self
    {
        return new self('El valor de nota no puede ser mayor a ' . $max);
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self('El valor de nota no puede ser menor a ' . $min);
    }

    public static function becauseValueIsOutOfRange(float $min, float $max): self
    {
        return new self(
            'La nota debe estar entre ' . $min . ' y ' . $max . '.'
        );
    }

}