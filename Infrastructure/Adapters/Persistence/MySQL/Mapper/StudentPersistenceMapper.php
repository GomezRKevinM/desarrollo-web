<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Mapper;

use App\Domain\Models\StudentModel;
use App\Domain\ValuesObjects\StudentId;
use App\Domain\ValuesObjects\StudentLastName;
use App\Domain\ValuesObjects\StudentName;
use App\Infrastructure\Adapters\Persistence\MySQL\Dto\StudentPersistenceDto;
use App\Infrastructure\Adapters\Persistence\MySQL\Entity\StudentEntity;

final class StudentPersistenceMapper
{
    public function fromModelToDto(StudentModel $Student): StudentPersistenceDto
    {
        return new StudentPersistenceDto(
            $Student->id()->value(),
            $Student->name()->value(),
            $Student->lastName()->value()
        );
    }

    public function fromDtoToEntity(StudentPersistenceDto $dto): StudentEntity
    {
        return new StudentEntity(
            $dto->id(),
            $dto->name(),
            $dto->lastName()
        );
    }

    public function fromRowToEntity(array $row): StudentEntity
    {
        return new StudentEntity(
            (string) $row['id'],
            (string) $row['name'],
            (string) $row['lastName'],
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }

    public function fromEntityToModel(StudentEntity $entity): StudentModel
    {
        return new StudentModel(
            new StudentId($entity->id()),
            new StudentName($entity->name()),
            new StudentLastName($entity->lastName()),
        );
    }

    public function fromRowToModel(array $row): StudentModel
    {
        return $this->fromEntityToModel(
            $this->fromRowToEntity($row)
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return StudentModel[]
     */
    public function fromRowsToModels(array $rows): array
    {
        $models = array();
        foreach ($rows as $row) {
            $models[] = $this->fromRowToModel($row);
        }
        return $models;
    }
}