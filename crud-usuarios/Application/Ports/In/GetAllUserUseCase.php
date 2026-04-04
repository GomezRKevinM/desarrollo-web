<?php

declare(strict_types = 1);

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\crud_usuarios\Domain\Models\UserModel;

interface GetAllUserUseCase
{
    /** @return UserModel[] */
    public function execute(GetAllUsersQuery $query): array;
}