<?php

declare(strict_types=1);

namespace App\Domain\ValuesObjects;

use App\Domain\Exceptions\InvalidCalificationCarreraException;

final class CalificationCarrera
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidCalificationCarreraException::becauseValueIsEmpty();
        }
        $this->value = $normalized;
    }

    public function value(): string { return $this->value; }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return $this->value; }

}