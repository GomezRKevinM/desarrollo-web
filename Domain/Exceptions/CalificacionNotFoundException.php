<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class CalificacionNotFoundException extends \DomainException
{
    public static function becauseIdWasNotFound(string $id): self
    {
        return new self('No se encontró una calificación con el ID: ' . $id);
    }

}