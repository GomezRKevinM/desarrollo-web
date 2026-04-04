<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Services;

use App\crud_usuarios\Application\Ports\In\GetAllUsersUseCase;
use App\crud_usuarios\Application\Ports\Out\GetAllUsersPort;
use App\crud_usuarios\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\crud_usuarios\Domain\Models\UserModel;

final class GetAllUsersService implements GetAllUsersUseCase
{
    private GetAllUsersPort $getAllUsersPort;

    public function __construct(GetAllUsersPort $getAllUsersPort)
    {
        $this->getAllUsersPort = $getAllUsersPort;
    }

    /** @return UserModel[] */
    public function execute(GetAllUsersQuery $query): array
    {
        return $this->getAllUsersPort->getAll();
    }
}
