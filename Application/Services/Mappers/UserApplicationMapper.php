<?php

declare(strict_types=1);

namespace App\Application\Services\Mappers;

use App\Application\Services\Dto\Commands\CreateUserCommand;
use App\Application\Services\Dto\Commands\DeleteUserCommand;
use App\Application\Services\Dto\Commands\UpdateUserCommand;
use App\Application\Services\Dto\Queries\GetUserByIdQuery;
use App\Domain\Enums\UserStatusEnum;
use App\Domain\Models\UserModel;
use App\Domain\ValuesObjects\UserEmail;
use App\Domain\ValuesObjects\UserId;
use App\Domain\ValuesObjects\UserName;
use App\Domain\ValuesObjects\UserPassword;


final class UserApplicationMapper
{
    /**
     * Construye un UserModel para CREAR.
     * Los Value Objects validan los datos: si algo es inválido, lanzan su excepción aquí.
     */
    public static function fromCreateCommandToModel(CreateUserCommand $command): UserModel
    {
        return new UserModel(
            new UserId($command->getId()),
            new UserName($command->getName()),
            new UserEmail($command->getEmail()),
            UserPassword::fromPlainText($command->getPassword()),
            $command->getRole(),
            UserStatusEnum::PENDING
        );
    }

    /**
     * Construye un UserModel para ACTUALIZAR.
     * Nota: la contraseña llega como plain text; UpdateUserService
     * decide si hashearla o reutilizar el hash existente ANTES de llamar a este método.
     */
    public static function fromUpdateCommandToModel(UpdateUserCommand $command): UserModel
    {
        return new UserModel(
            new UserId($command->getId()),
            new UserName($command->getName()),
            new UserEmail($command->getEmail()),
            UserPassword::fromPlainText($command->getPassword()),
            $command->getRole(),
            $command->getStatus()
        );
    }

    /**
     * Extrae el UserId desde una GetUserByIdQuery.
     */
    public static function fromGetUserByIdQueryToUserId(GetUserByIdQuery $query): UserId
    {
        return new UserId($query->getId());
    }

    /**
     * Extrae el UserId desde un DeleteUserCommand.
     */
    public static function fromDeleteCommandToUserId(DeleteUserCommand $command): UserId
    {
        return new UserId($command->getId());
    }

    /**
     * Convierte un UserModel a array simple (para respuestas HTTP).
     *
     * @return array<string, string>
     */
    public static function fromModelToArray(UserModel $user): array
    {
        return [
            'id'       => $user->id()->value(),
            'name'     => $user->name()->value(),
            'email'    => $user->email()->value(),
            'password' => $user->password()->value(),
            'role'     => $user->role(),
            'status'   => $user->status(),
        ];
    }

    /**
     * Convierte un array de UserModel a array de arrays simples.
     *
     * @param  UserModel[]              $users
     * @return array<int, array<string, string>>
     */
    public static function fromModelsToArray(array $users): array
    {
        $result = [];
        foreach ($users as $user) {
            $result[] = self::fromModelToArray($user);
        }
        return $result;
    }
}