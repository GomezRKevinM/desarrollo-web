<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\crud_usuarios\Domain\Models\UserModel;

interface GetByUserIdUseCase
{
    public function execute(GetUserByIdQuery $query): ?UserModel;
}