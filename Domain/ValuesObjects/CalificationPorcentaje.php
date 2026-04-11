<?php

namespace App\Domain\ValuesObjects;

use App\Domain\Exceptions\InvalidCalificationPorcentajeException;

final class CalificationPorcentaje
{
    private const MIN = 0.01;
    private const MAX = 1.00;
    private float $value;

    public function __construct(float $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw InvalidCalificationPorcentajeException::becauseValueIsOutOfRange(self::MIN, self::MAX);
        }
        $this->value = round($value, 2);
    }

    public function value(): float { return $this->value; }

    public function equals(self $other): bool { return $this->value === $other->value; }

    public function __toString(): string { return (string) $this->value; }

}