<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class StudentNotFoundException extends \DomainException
{
    public static function becauseIdWasNotFound(string $id): self
    {
        return new self('No se encontró un estudiante con el ID: ' . $id);
    }

    public static function becauseNameNameWasNotFound(string $name): self
    {
        return new self('No se encontró un estudiante con el nombre: ' . $name);
    }

    public static function becauseLastNameWasNotFound(string $lastName): self
    {
        return new self('No se encontró un estudiante con el apellido: ' . $lastName);
    }
}