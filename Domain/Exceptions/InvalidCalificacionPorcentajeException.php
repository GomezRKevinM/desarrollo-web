<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificacionPorcentajeException extends \InvalidArgumentException
{

    public static function becauseIsEmpty(): self
    {
        return new self('el valor del periodo no puede estar vacio');
    }
    public static function becauseValueIsOutOfRange(float $min, float $max): self
    {
        return new self(
            'El porcentaje debe estar entre ' . $min . ' y ' . $max . '.'
        );
    }

    public static function becauseSumExceedsHundred(float $current, float $incoming): self
    {
        return new self(
            'La suma de porcentajes (' . ($current + $incoming) . '%) supera el 100% permitido.'
        );
    }
}