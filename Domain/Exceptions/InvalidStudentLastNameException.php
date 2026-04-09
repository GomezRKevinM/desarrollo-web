<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidStudentLastNameException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El apellido del estudiante no puede estar vacío.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self('El apellido del estudiante debe tener al menos ' . $min . ' caracteres.');
    }
}