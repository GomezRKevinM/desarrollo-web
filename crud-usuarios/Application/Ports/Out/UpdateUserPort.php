<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\Out;

use App\crud_usuarios\Domain\Models\UserModel;

require_once __DIR__ . '/../../../Domain/Models/UserModel.php';

interface UpdateUserPort
{
    public function update(UserModel $user): UserModel;
}