<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\DeleteUserUseCase;
use App\Application\Ports\Out\DeleteUserPort;
use App\Application\Ports\Out\GetUserByIdPort;
use App\Application\Services\Dto\Commands\DeleteUserCommand;
use App\Application\Services\Mappers\UserApplicationMapper;
use App\Domain\Exceptions\UserNotFoundException;

final class DeleteUserService implements DeleteUserUseCase
{
    private DeleteUserPort $deleteUserPort;
    private GetUserByIdPort $getUserByIdPort;

    public function __construct(
        DeleteUserPort $deleteUserPort,
        GetUserByIdPort $getUserByIdPort
    ) {
        $this->deleteUserPort  = $deleteUserPort;
        $this->getUserByIdPort = $getUserByIdPort;
    }

    public function execute(DeleteUserCommand $command): void
    {
        // 1. Extrae el UserId del comando
        $userId = UserApplicationMapper::fromDeleteCommandToUserId($command);

        // 2. Verifica que el usuario existe antes de eliminar
        $existingUser = $this->getUserByIdPort->getById($userId);
        if ($existingUser === null) {
            throw UserNotFoundException::becauseIdWasNotFound($userId->value());
        }

        // 3. Elimina
        $this->deleteUserPort->delete($userId);
    }
}