<?php

namespace App\Domain\Exceptions;

class StudentAlreadyExistsException extends \DomainException
{
    public static function becauseFullNameAlreadyExists(string $fullName): self
    {
        return new self('Ya existe un estudiante con el nombre: ' . $fullName);
    }
}