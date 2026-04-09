<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\Domain\Models\UserModel;

interface GetByUserIdUseCase
{
    public function execute(GetUserByIdQuery $query): ?UserModel;
}