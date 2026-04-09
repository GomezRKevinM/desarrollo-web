<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidStudentNameException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre del estudiante no puede estar vacío.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self('El nombre del estudiante debe tener al menos ' . $min . ' caracteres.');
    }
}