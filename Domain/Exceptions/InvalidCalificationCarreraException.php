<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationCarreraException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('La carrera no puede estar vacía.');
    }

    public static function becauseLengthIsTooLong(int $max): self
    {
        return new self('La carrera no puede superar los ' . $max . ' caracteres.');
    }
}