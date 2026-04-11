<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationDocenteException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre del docente no puede estar vacío.');
    }

    public static function becauseLengthIsTooLong(int $max): self
    {
        return new self('El nombre del docente no puede superar los ' . $max . ' caracteres.');
    }
}