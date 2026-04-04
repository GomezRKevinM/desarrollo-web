<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

final class InvalidUserPasswordException extends \InvalidArgumentException
{

    public static function becauseValueIsEmpty(): self
    {
        return new self('La contraseña no puede estar vacía.');
    }

    public static function becauseLengthIsTooShort(int $min): self
    {
        return new self('La contraseña debe tener al menos ' . $min . ' caracteres.');
    }

}