<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetAllUsersUseCase;
use App\Application\Ports\Out\GetAllUsersPort;
use App\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\Domain\Models\UserModel;

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
