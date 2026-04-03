<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidUserNameException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre del usuario no puede estar vacío.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self('El nombre del usuario debe tener al menos ' . $min . ' caracteres.');
    }
}