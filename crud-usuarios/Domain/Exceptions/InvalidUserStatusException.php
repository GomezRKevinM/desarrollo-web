<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class InvalidUserStatusException extends \InvalidArgumentException
{

    public static function becauseValueIsInvalid(string $value): self
    {
        return new self('El estado "' . $value . '" no es un estado válido.');
    }

}