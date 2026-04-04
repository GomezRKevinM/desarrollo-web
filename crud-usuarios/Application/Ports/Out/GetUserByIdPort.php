<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\Out;

use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Domain\ValuesObjects\UserId;

interface GetUserByIdPort
{
    // Retorna null si no existe (no lanza excepción)
    public function getById(UserId $userId): ?UserModel;
}