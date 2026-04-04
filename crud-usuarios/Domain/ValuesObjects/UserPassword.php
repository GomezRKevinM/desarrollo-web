<?php

declare(strict_types = 1);

namespace App\crud_usuarios\Domain\ValuesObjects;

use App\crud_usuarios\Domain\Exceptions\InvalidUserPasswordException;
class UserPassword
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidUserPasswordException::becauseValueIsEmpty();
        }
        if (strlen($normalized) < 8) {
            throw InvalidUserPasswordException::becauseLengthIsTooShort(8);
        }
        $this->value = $normalized;
    }

    public static function fromPlainText(string $raw): self
    {
        $instance = new self($raw);
        $instance->value = password_hash($raw, PASSWORD_BCRYPT);
        return $instance;
    }

    public static function fromHash(string $hash): self
    {
        $instance = new self($hash);
        return $instance;
    }

    public function verifyPlain(string $plain): bool
    {
        return password_verify($plain, $this->value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(UserPassword $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}