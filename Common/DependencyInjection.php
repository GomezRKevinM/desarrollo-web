<?php

declare(strict_types=1);

namespace App\Common;

use App\Application\Ports\In\CreateStudentUseCase;
use App\Application\Ports\In\CreateUserUseCase;
use App\Application\Ports\In\DeleteUserUseCase;
use App\Application\Ports\In\GetAllStudentsUseCase;
use App\Application\Ports\In\GetAllUsersUseCase;
use App\Application\Ports\In\GetByUserIdUseCase;
use App\Application\Ports\In\LoginUseCase;
use App\Application\Ports\In\UpdateStudentUseCase;
use App\Application\Ports\In\DeleteStudentUseCase;
use App\Application\Ports\In\GetByStudentIdUseCase;
use App\Application\Ports\In\UpdateUserUseCase;
use App\Application\Services\CreateStudentService;
use App\Application\Services\CreateUSerService;
use App\Application\Services\DeleteUserService;
use App\Application\Services\DeleteStudentService;
use App\Application\Services\GetAllUsersService;
use App\Application\Services\GetAllStudentsService;
use App\Application\Services\GetUserByIdService;
use App\Application\Services\GetStudentByIdService;
use App\Application\Services\LoginService;
use App\Application\Services\UpdateUserService;
use App\Application\Services\UpdateStudentService;
use App\Infrastructure\Adapters\Persistence\MySQL\Config\Connection;
use App\Infrastructure\Adapters\Persistence\MySQL\Mapper\StudentPersistenceMapper;
use App\Infrastructure\Adapters\Persistence\MySQL\Mapper\UserPersistenceMapper;
use App\Infrastructure\Adapters\Persistence\MySQL\Repository\StudentRepositoryMySQL;
use App\Infrastructure\Adapters\Persistence\MySQL\Repository\UserRepositoryMySQL;
use App\Infrastructure\Entrypoints\Web\Controllers\StudentController;
use App\Infrastructure\Entrypoints\Web\Controllers\UserController;
use App\Infrastructure\Entrypoints\Web\Controllers\Mapper\UserWebMapper;
use App\Infrastructure\Entrypoints\Web\Controllers\Mapper\StudentWebMapper;
use RuntimeException;

final class DependencyInjection
{
    private const DEFAULT_ENV = [
        'DB_HOST' => '127.0.0.1',
        'DB_PORT' => '3306',
        'DB_NAME' => 'crud_usuarios',
        'DB_USER' => 'root',
        'DB_PASS' => 'root',
    ];

    private static array $env = [];

    private static function getConnection(): Connection
    {
        return new Connection(
            host: self::getEnv('DB_HOST'),
            port: self::getEnv('DB_PORT'),
            database: self::getEnv('DB_NAME'),
            username: self::getEnv('DB_USER'),
            password: self::getEnv('DB_PASS')
        );
    }

    private static function getEnv(string $name): string
    {
        if (empty(self::$env)) {
            self::loadEnv();
        }

        return $_ENV[$name] ?? $_SERVER[$name] ?? self::$env[$name] ?? self::DEFAULT_ENV[$name] ?? throw new RuntimeException("Missing environment variable: {$name}");
    }

    private static function loadEnv(): void
    {
        $path = dirname(__DIR__) . '/.env';

        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $value = trim($parts[1]);

            if ($name === '') {
                continue;
            }

            $value = trim($value, "\"'");
            self::$env[$name] = $value;
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public static function getUserRepository(): UserRepositoryMySQL
    {
        return new UserRepositoryMySQL(
            pdo: self::getConnection()->createPDO(),
            mapper: new UserPersistenceMapper(),
        );
    }

    public static function getStudentRepository(): StudentRepositoryMySQL
    {
        return new StudentRepositoryMySQL(
            pdo: self::getConnection()->createPDO(),
            mapper: new StudentPersistenceMapper(),
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

    public static function getCreateStudentUseCase(): CreateStudentUseCase
    {
        $repo = self::getStudentRepository();
        return new CreateStudentService($repo, $repo);
    }

    public static function getUpdateStudentUseCase(): UpdateStudentUseCase
    {
        $repo = self::getStudentRepository();
        return new UpdateStudentService($repo, $repo, $repo);
    }

    public static function getGetStudentByIdUseCase(): GetByStudentIdUseCase
    {
        return new GetStudentByIdService(self::getStudentRepository());
    }

    public static function getGetAllStudentsUseCase(): GetAllStudentsUseCase
    {
        return new GetAllStudentsService(self::getStudentRepository());
    }

    public static function getDeleteStudentUseCase(): DeleteStudentUseCase
    {
        $repo = self::getStudentRepository();
        return new DeleteStudentService($repo, $repo);
    }

    public static function getStudentController(): StudentController
    {
        return new StudentController(
            createStudentUseCase:  self::getCreateStudentUseCase(),
            updateStudentUseCase:  self::getUpdateStudentUseCase(),
            getStudentByIdUseCase: self::getGetStudentByIdUseCase(),
            getAllStudentsUseCase:  self::getGetAllStudentsUseCase(),
            deleteStudentUseCase:  self::getDeleteStudentUseCase(),
            mapper:             new StudentWebMapper(),
        );
    }

}