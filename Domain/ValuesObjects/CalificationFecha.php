<?php

declare(strict_types=1);

namespace App\Domain\ValuesObjects;

use App\Domain\Exceptions\InvalidCalificationFechaException;

final class CalificationFecha
{
    private \DateTimeImmutable $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidCalificationFechaException::becauseIsEmpty();
        }

        $parsed = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $normalized)
            ?: \DateTimeImmutable::createFromFormat('Y-m-d', $normalized);

        if ($parsed === false) {
            throw InvalidCalificationFechaException::becauseFormatIsInvalid($normalized);
        }

        $this->value = $parsed;
    }

    public static function now(): self
    {
        $instance = new self(date('Y-m-d H:i:s'));
        return $instance;
    }

    public function value(): \DateTimeImmutable { return $this->value; }

    public function toDbFormat(): string
    {
        return $this->value->format('Y-m-d H:i:s');
    }

    public function equals(self $other): bool
    {
        return $this->value == $other->value;
    }

    public function __toString(): string { return $this->toDbFormat(); }

}