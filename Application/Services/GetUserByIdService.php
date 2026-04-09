<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetByUserIdUseCase;
use App\Application\Ports\Out\GetUserByIdPort;
use App\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\Application\Services\Mappers\UserApplicationMapper;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Models\UserModel;

final class GetUserByIdService implements GetByUserIdUseCase
{
    private GetUserByIdPort $getUserByIdPort;

    public function __construct(GetUserByIdPort $getUserByIdPort)
    {
        $this->getUserByIdPort = $getUserByIdPort;
    }

    public function execute(GetUserByIdQuery $query): UserModel
    {
        // 1. Convierte la Query a UserId
        $userId = UserApplicationMapper::fromGetUserByIdQueryToUserId($query);

        // 2. Busca el usuario
        $user = $this->getUserByIdPort->getById($userId);
        if ($user === null) {
            throw UserNotFoundException::becauseIdWasNotFound($userId->value());
        }

        return $user;
    }
}