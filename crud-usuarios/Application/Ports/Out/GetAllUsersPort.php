<?php

namespace App\crud_usuarios\Application\Ports\Out;

use App\crud_usuarios\Domain\Models\UserModel;

interface GetAllUsersPort
{
    /** @return UserModel[] */
    public function getAll(): array;
}