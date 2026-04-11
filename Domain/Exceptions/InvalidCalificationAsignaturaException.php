<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationAsignaturaException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('La asignatura no puede estar vacía.');
    }

    public static function becauseLengthIsTooLong(int $max): self
    {
        return new self('La asignatura no puede superar los ' . $max . ' caracteres.');
    }
}