<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidUserIdException;
class UserId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidUserIdException::becauseValueIsEmpty();
        }
        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserId $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}