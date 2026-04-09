<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\UserModel;

interface UpdateUserPort
{
    public function update(UserModel $user): UserModel;
}