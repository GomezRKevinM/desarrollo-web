<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationPeriodoException extends \InvalidArgumentException
{

    public static function becauseIsEmpty(): self
    {
        return new self('el periodo no puede estar vacío.');
    }
    public static function becauseValueIsNotValid(): self
    {
        return new self('el valor del periodo no es válido.');
    }

    public static function becauseLengthIsTooLong(int $max): self
    {
        return new self('el valor del periodo no puede ser mayor que ' . $max);
    }
}