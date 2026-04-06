<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Repository;

use App\crud_usuarios\Application\Ports\Out\DeleteUserPort;
use App\crud_usuarios\Application\Ports\Out\GetAllUsersPort;
use App\crud_usuarios\Application\Ports\Out\GetUserByEmailPort;
use App\crud_usuarios\Application\Ports\Out\GetUserByIdPort;
use App\crud_usuarios\Application\Ports\Out\SaveUserPort;
use App\crud_usuarios\Application\Ports\Out\UpdateUserPort;
use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Domain\ValuesObjects\UserEmail;
use App\crud_usuarios\Domain\ValuesObjects\UserId;
use App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Mapper\UserPersistenceMapper;
use PDO;
use RuntimeException;

final class UserRepositoryMySQL implements
    SaveUserPort,
    UpdateUserPort,
    GetUserByIdPort,
    GetUserByEmailPort,
    GetAllUsersPort,
    DeleteUserPort
{

    private PDO $pdo;
    private UserPersistenceMapper $mapper;

    public function __construct(PDO $pdo, UserPersistenceMapper $mapper)
    {
        $this->pdo = $pdo;
        $this->mapper = $mapper;
    }

    public function save(UserModel $user): UserModel
    {
        $dto = $this->mapper->fromModelToDto($user);
        $sql = <<<'SQL'
            INSERT INTO users (
                id,
                name,
                email,
                password,
                role,
                status,
                created_at,
                updated_at
            ) VALUES (
                :id,
                :name,
                :email,
                :password,
                :role,
                :status,
                NOW(),
                NOW()
            )
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $dto->id(),
            ':name' => $dto->name(),
            ':email' => $dto->email(),
            ':password' => $dto->password(),
            ':role' => $dto->role(),
            ':status' => $dto->status(),
        ));
        $savedUser = $this->getById(new UserId($dto->id()));
        if ($savedUser === null) {
            throw new RuntimeException('The user could not be recovered after save.');
        }
        return $savedUser;
    }

    public function update(UserModel $user): UserModel
    {
        $dto = $this->mapper->fromModelToDto($user);
        $sql = <<<'SQL'
            UPDATE users
            SET name = :name,
            email = :email,
            password = :password,
            role = :role,
            status = :status,
            updated_at = NOW()
            WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $dto->id(),
            ':name' => $dto->name(),
            ':email' => $dto->email(),
            ':password' => $dto->password(),
            ':role' => $dto->role(),
            ':status' => $dto->status(),
        ));
        $updatedUser = $this->getById(new UserId($dto->id()));
        if ($updatedUser === null) {
            throw new RuntimeException('The user could not be recovered after update.');
        }
        return $updatedUser;
    }

    public function getById(UserId $userId): ?UserModel
    {
        $sql = <<<'SQL'
            SELECT
            id,
            name,
            email,
            password,
            role,
            status,
            created_at,
            updated_at
            FROM users
            WHERE id = :id
            LIMIT 1
        SQL;
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $userId->value(),
        ));
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }
        return $this->mapper->fromRowToModel($row);
    }

    public function getByEmail(UserEmail $email): ?UserModel
    {
        $sql = <<<'SQL'
            SELECT
            id,
            name,
            email,
            password,
            role,
            status,
            created_at,
            updated_at
            FROM users
            WHERE email = :email
            LIMIT 1
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':email' => $email->value(),
        ));
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }
        return $this->mapper->fromRowToModel($row);
    }

    /**
     * @return UserModel[]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT
            id,
            name,
            email,
            password,
            role,
            status,
            created_at,
            updated_at
            FROM users
            ORDER BY name ASC
        SQL;

        $statement = $this->pdo->query($sql);
        $rows = $statement->fetchAll();
        return $this->mapper->fromRowsToModels($rows);
    }

    public function delete(UserId $userId): void
    {
        $sql = <<<'SQL'
            DELETE FROM users WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $userId->value(),
        ));
    }

}