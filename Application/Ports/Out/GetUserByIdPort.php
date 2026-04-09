<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\UserModel;
use App\Domain\ValuesObjects\UserId;

interface GetUserByIdPort
{
    // Retorna null si no existe (no lanza excepción)
    public function getById(UserId $userId): ?UserModel;
}