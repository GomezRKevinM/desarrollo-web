<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Exceptions;

final class InvalidUserRoleException extends \InvalidArgumentException
{
    public static function becauseValueIsInvalid(string $value): self
    {
        return new self('El rol "' . $value . '" no es un rol válido.');
    }

}