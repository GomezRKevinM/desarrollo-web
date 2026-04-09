<?php

declare(strict_types=1);

namespace App\Domain\Enums;

use App\Domain\Exceptions\InvalidUserRoleException;

class UserRoleEnum
{
    const ADMIN    = 'ADMIN';
    const MEMBER   = 'MEMBER';
    const REVIEWER = 'REVIEWER';

    public static function values(): array
    {
        return [self::ADMIN, self::MEMBER, self::REVIEWER];
    }

    public static function isValid(string $value): bool
    {
        return in_array($value, self::values(), true);
    }

    public static function ensureIsValid(string $value): void
    {
        if (!self::isValid($value)) {
            throw InvalidUserRoleException::becauseValueIsInvalid($value);
        }
    }

}
