<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

final class UserAlreadyExistsException extends \DomainException
{

    public static function becauseEmailAlreadyExists(string $email): self
    {
        return new self('Ya existe un usuario con el email: ' . $email);
    }

}