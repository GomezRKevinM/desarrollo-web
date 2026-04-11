<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class CalificationAlreadyExistsException extends \DomainException
{
    public static function becauseIdAlreadyExists(string $id): self
    {
        return new self('Ya existe una calificación con el ID: ' . $id);
    }
}