<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationUniversidadException extends \InvalidArgumentException
{
    public static function becauseValueIsEmpty(): self
    {
        return new self('El nombre de la universidad no puede estar vacío.');
    }

    public static function becauseValueIsNotValid(): self
    {
        return new self('El nombre de la universidad no es válido.');
    }
}