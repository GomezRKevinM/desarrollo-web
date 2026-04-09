<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class UserNotFoundException extends \DomainException
{

    public static function becauseIdWasNotFound(string $id): self
    {
        return new self('No se encontró un usuario con el ID: ' . $id);
    }

}