<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

final class InvalidCalificationActividadEvaluadaException extends \InvalidArgumentException
{
    public static function becauseIsEmpty(): self
    {
        return new self('La actividad evaluada no puede estar vacía.');
    }

}