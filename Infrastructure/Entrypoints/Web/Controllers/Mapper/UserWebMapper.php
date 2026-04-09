<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Mapper;

use App\Application\Services\Dto\Commands\CreateUserCommand;
use App\Application\Services\Dto\Commands\DeleteUserCommand;
use App\Application\Services\Dto\Commands\LoginCommand;
use App\Application\Services\Dto\Commands\UpdateUserCommand;
use App\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\Domain\Models\UserModel;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateUserWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateUserWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\LoginWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UserResponse;

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