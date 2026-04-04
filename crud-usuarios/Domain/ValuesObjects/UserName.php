<?php

declare(strict_types = 1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidUserNameException;

class UserName
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidUserNameException::becauseValueIsEmpty();
        }
        if (mb_strlen($normalized) < 3) {
            throw InvalidUserNameException::becauseLengthIsTooShort(3);
        }
        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserName $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}