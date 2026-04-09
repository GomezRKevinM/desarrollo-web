<?php

namespace App\Application\Ports\Out;

use App\Domain\Models\UserModel;

interface GetAllUsersPort
{
    /** @return UserModel[] */
    public function getAll(): array;
}