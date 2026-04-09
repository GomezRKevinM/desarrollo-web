<?php

namespace App\Application\Ports\Out;

use App\Domain\Models\UserModel;
use App\Domain\ValuesObjects\UserEmail;

interface GetUserByEmailPort
{
    // Retorna null si no existe (usado para verificar duplicados de email)
    public function getByEmail(UserEmail $email): ?UserModel;
}