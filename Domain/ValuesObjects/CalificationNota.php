<?php

declare(strict_types=1);

namespace App\Domain\ValuesObjects;

use App\Domain\Exceptions\InvalidCalificationNotaException;

final class CalificationNota
{
    private const MIN = 1.0;
    private const MAX = 5.0;
    private float $value;

    public function __construct(float $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw InvalidCalificationNotaException::becauseValueIsOutOfRange(self::MIN, self::MAX);
        }
        $this->value = round($value, 2);
    }

    public function value(): float { return $this->value; }

    public function isApproved(float $passingGrade = 3.0): bool
    {
        return $this->value >= $passingGrade;
    }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return (string) $this->value; }
}