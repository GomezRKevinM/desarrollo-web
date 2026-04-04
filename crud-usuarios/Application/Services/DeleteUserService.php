<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Services;

use App\crud_usuarios\Application\Ports\In\DeleteUserUseCase;
use App\crud_usuarios\Application\Ports\Out\DeleteUserPort;
use App\crud_usuarios\Application\Ports\Out\GetUserByIdPort;
use App\crud_usuarios\Application\Services\Dto\Commands\DeleteUserCommand;
use App\crud_usuarios\Application\Services\Mappers\UserApplicationMapper;
use App\crud_usuarios\Domain\Exceptions\UserNotFoundException;

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