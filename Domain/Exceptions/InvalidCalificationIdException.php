<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationIdException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El ID de la calificación no puede estar vacío.');
    }
}