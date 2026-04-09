<?php

declare(strict_types = 1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\Domain\Models\UserModel;

interface GetAllUsersUseCase
{
    /** @return UserModel[] */
    public function execute(GetAllUsersQuery $query): array;
}