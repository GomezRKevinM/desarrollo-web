<?php

declare(strict_types=1);

namespace App\Domain\ValuesObjects;

use App\Domain\Exceptions\InvalidStudentLastNameException;

class StudentLastName
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = trim($value);
        if ($normalized === '') {
            throw InvalidStudentLastNameException::becauseValueIsEmpty();
        }
        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(StudentLastName $other): bool
    {
        return $this->value === $other->value();
    }

    public function __toString(): string
    {
        return $this->value;
    }
}