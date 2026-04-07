<?php

declare(strict_types=1);

namespace App\Common;

use App\crud_usuarios\Application\Ports\In\CreateUserUseCase;
use App\crud_usuarios\Application\Ports\In\DeleteUserUseCase;
use App\crud_usuarios\Application\Ports\In\GetAllUsersUseCase;
use App\crud_usuarios\Application\Ports\In\GetByUserIdUseCase;
use App\crud_usuarios\Application\Ports\In\LoginUseCase;
use App\crud_usuarios\Application\Ports\In\UpdateUserUseCase;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Config\Connection;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Mapper\UserPersistenceMapper;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Repository\UserRepositoryMySQL;

final class DependencyInjection
{
    private static function getConnection(): Connection
    {
        return new Connection(
            host: '127.0.0.1',
            port: '3306',
            database: 'crud_usuarios',
            username: 'root',
            password: ''
        );
    }

    private static function getUserRepository(): UserRepositoryMySQL
    {
        return new UserRepositoryMySQL(
            pdo: self::getConnection()->createPDO(),
            mapper: new UserPersistenceMapper(),
        );
    }

    public static function getCreateUserUseCase(): CreateUserUseCase
    {
        $repo = self::getUserRepository();
        return new CreateUSerService($repo, $repo);
    }

    public static function getUpdateUserUseCase(): UpdateUserUseCase
    {
        $repo = self::getUserRepository();
        return new UpdateUserService($repo, $repo, $repo);
    }

    public static function getDeleteUserUseCase(): DeleteUserUseCase
    {
        $repo = self::getUserRepository();
        return new DeleteUserService($repo, $repo);
    }

    public static function getGetUserByIdUseCase(): GetByUserIdUseCase
    {
        return new GetUserByIdService(self::getUserRepository());
    }

    public static function getGetAllUsersUseCase(): GetAllUsersUseCase
    {
        return new GetAllUsersService(self::getUserRepository());
    }

    public static function getLoginUseCase(): LoginUseCase
    {
        return new LoginService(self::getUserRepository());
    }

    public static function getUserController(): UserController
    {
        return new UserController(
            createUserUseCase:  self::getCreateUserUseCase(),
            updateUserUseCase:  self::getUpdateUserUseCase(),
            getUserByIdUseCase: self::getGetUserByIdUseCase(),
            getAllUsersUseCase:  self::getGetAllUsersUseCase(),
            deleteUserUseCase:  self::getDeleteUserUseCase(),
            mapper:             new UserWebMapper(),
        );
    }
}