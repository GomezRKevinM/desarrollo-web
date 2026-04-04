<?php

namespace App\crud_usuarios\Application\Ports\Out;

use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Domain\ValuesObjects\UserEmail;

interface GetUserByEmailPort
{
    // Retorna null si no existe (usado para verificar duplicados de email)
    public function getByEmail(UserEmail $email): ?UserModel;
}