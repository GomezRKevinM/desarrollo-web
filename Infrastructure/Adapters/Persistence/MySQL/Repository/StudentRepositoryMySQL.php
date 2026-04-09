<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Repository;

use App\Application\Ports\Out\DeleteStudentPort;
use App\Application\Ports\Out\GetAllStudentsPort;
use App\Application\Ports\Out\GetStudentByIdPort;
use App\Application\Ports\Out\SaveStudentPort;
use App\Application\Ports\Out\UpdateStudentPort;
use App\Domain\Models\StudentModel;
use App\Domain\ValuesObjects\StudentId;
use App\Infrastructure\Adapters\Persistence\MySQL\Mapper\StudentPersistenceMapper;
use PDO;
use RuntimeException;

final class StudentRepositoryMySQL implements
    SaveStudentPort,
    UpdateStudentPort,
    GetStudentByIdPort,
    GetAllStudentsPort,
    DeleteStudentPort
{

    private PDO $pdo;
    private StudentPersistenceMapper $mapper;

    public function __construct(PDO $pdo, StudentPersistenceMapper $mapper)
    {
        $this->pdo = $pdo;
        $this->mapper = $mapper;
    }

    public function save(StudentModel $Student): StudentModel
    {
        $dto = $this->mapper->fromModelToDto($Student);
        $sql = <<<'SQL'
            INSERT INTO students (
                id,
                name,
                lastName,
                created_at,
                updated_at
            ) VALUES (
                :id,
                :name,
                :lastName,
                NOW(),
                NOW()
            )
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $dto->id(),
            ':name' => $dto->name(),
            ':lastName' => $dto->lastName()
        ));
        $savedStudent = $this->getById(new StudentId($dto->id()));
        if ($savedStudent === null) {
            throw new RuntimeException('The Student could not be recovered after save.');
        }
        return $savedStudent;
    }

    public function update(StudentModel $Student): StudentModel
    {
        $dto = $this->mapper->fromModelToDto($Student);
        $sql = <<<'SQL'
            UPDATE students
            SET name = :name,
            lastName = :lastName,
            updated_at = NOW()
            WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $dto->id(),
            ':name' => $dto->name(),
            ':lastName' => $dto->lastName()
        ));
        $updatedStudent = $this->getById(new StudentId($dto->id()));
        if ($updatedStudent === null) {
            throw new RuntimeException('The Student could not be recovered after update.');
        }
        return $updatedStudent;
    }

    public function getById(StudentId $StudentId): ?StudentModel
    {
        $sql = <<<'SQL'
            SELECT
            id,
            name,
            lastName,
            created_at,
            updated_at
            FROM students
            WHERE id = :id
            LIMIT 1
        SQL;
        
        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $StudentId->value(),
        ));
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }
        return $this->mapper->fromRowToModel($row);
    }

    /**
     * @return StudentModel[]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT
            id,
            name,
            lastName,
            created_at,
            updated_at
            FROM students
            ORDER BY name ASC
        SQL;

        $statement = $this->pdo->query($sql);
        $rows = $statement->fetchAll();
        return $this->mapper->fromRowsToModels($rows);
    }

    public function delete(StudentId $StudentId): void
    {
        $sql = <<<'SQL'
            DELETE FROM students WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $StudentId->value(),
        ));
    }

}