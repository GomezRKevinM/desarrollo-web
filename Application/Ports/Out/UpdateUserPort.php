<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\UserModel;

require_once __DIR__ . '/../../../Domain/Models/UserModel.php';

interface UpdateUserPort
{
    public function update(UserModel $user): UserModel;
}