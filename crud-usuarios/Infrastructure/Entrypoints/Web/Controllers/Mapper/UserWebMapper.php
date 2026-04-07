<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Mapper;

use App\crud_usuarios\Application\Services\Dto\Commands\CreateUserCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\DeleteUserCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\LoginCommand;
use App\crud_usuarios\Application\Services\Dto\Commands\UpdateUserCommand;
use App\crud_usuarios\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Dto\CreateUserWebRequest;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Dto\UpdateUserWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\LoginWebRequest;
use App\crud_usuarios\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;

final class UserWebMapper
{
    public function fromCreateRequestToCommand(CreateUserWebRequest $request): CreateUserCommand
    {
        return new CreateUserCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            email:    $request->getEmail(),
            password: $request->getPassword(),
            role:     $request->getRole(),
        );
    }

    public function fromUpdateRequestToCommand(UpdateUserWebRequest $request): UpdateUserCommand
    {
        return new UpdateUserCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            email:    $request->getEmail(),
            password: $request->getPassword(),
            role:     $request->getRole(),
            status:   $request->getStatus(),
        );
    }

    public function fromLoginRequestToCommand(LoginWebRequest $request): LoginCommand
    {
        return new LoginCommand(
            email:    $request->getEmail(),
            password: $request->getPassword(),
        );
    }

    public function fromIdToGetByIdQuery(string $id): GetUserByIdQuery
    {
        return new GetUserByIdQuery($id);
    }

    public function fromIdToDeleteCommand(string $id): DeleteUserCommand
    {
        return new DeleteUserCommand($id);
    }

    public function fromModelToResponse(UserModel $user): UserResponse
    {
        return new UserResponse(
            id:     $user->id()->value(),
            name:   $user->name()->value(),
            email:  $user->email()->value(),
            role:   $user->role(),
            status: $user->status(),
        );
    }

    /**
     * @param  UserModel[]   $users
     * @return UserResponse[]
     */
    public function fromModelsToResponses(array $users): array
    {
        return array_map(
            fn(UserModel $user): UserResponse => $this->fromModelToResponse($user),
            $users,
        );
    }

}