<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers;

use App\crud_usuarios\Application\Ports\In\CreateUserUseCase;
use App\crud_usuarios\Application\Ports\In\DeleteUserUseCase;
use App\crud_usuarios\Application\Ports\In\GetAllUsersUseCase;
use App\crud_usuarios\Application\Ports\In\GetByUserIdUseCase;
use App\crud_usuarios\Application\Ports\In\UpdateUserUseCase;
use App\crud_usuarios\Application\Services\Dto\Queries\GetAllUsersQuery;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Dto\CreateUserWebRequest;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Dto\UpdateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Mapper\UserWebMapper;

final class UserController
{
    public function __construct(
        private readonly CreateUserUseCase  $createUserUseCase,
        private readonly UpdateUserUseCase  $updateUserUseCase,
        private readonly GetByUserIdUseCase $getUserByIdUseCase,
        private readonly GetAllUsersUseCase $getAllUsersUseCase,
        private readonly DeleteUserUseCase  $deleteUserUseCase,
        private readonly UserWebMapper      $mapper,
    ) {}

    /** @return UserResponse[] */
    public function index(): array
    {
        $users = $this->getAllUsersUseCase->execute(new GetAllUsersQuery());
        return $this->mapper->fromModelsToResponses($users);
    }

    public function show(string $id): UserResponse
    {
        $query = $this->mapper->fromIdToGetByIdQuery($id);
        $user  = $this->getUserByIdUseCase->execute($query);
        return $this->mapper->fromModelToResponse($user);
    }

    public function store(CreateUserWebRequest $request): UserResponse
    {
        $command = $this->mapper->fromCreateRequestToCommand($request);
        $user    = $this->createUserUseCase->execute($command);
        return $this->mapper->fromModelToResponse($user);
    }

    public function update(UpdateUserWebRequest $request): UserResponse
    {
        $command = $this->mapper->fromUpdateRequestToCommand($request);
        $user    = $this->updateUserUseCase->execute($command);
        return $this->mapper->fromModelToResponse($user);
    }

    public function delete(string $id): void
    {
        $command = $this->mapper->fromIdToDeleteCommand($id);
        $this->deleteUserUseCase->execute($command);
    }

}