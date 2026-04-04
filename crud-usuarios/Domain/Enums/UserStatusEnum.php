<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Enums;

use App\crud_usuarios\Domain\Exceptions\InvalidUserStatusException;

class UserStatusEnum
{
    const ACTIVE   = 'ACTIVE';
    const INACTIVE = 'INACTIVE';
    const PENDING  = 'PENDING';
    const BLOCKED  = 'BLOCKED';

    public static function values(): array
    {
        return [self::ACTIVE, self::INACTIVE, self::PENDING, self::BLOCKED];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function ensureIsValid(string $value): void
    {
        if (!self::isValid($value)) {
            throw InvalidUserStatusException::becauseValueIsInvalid($value);
        }
    }
}
