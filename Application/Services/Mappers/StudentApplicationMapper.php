<?php

declare(strict_types=1);

namespace App\Application\Services\Mappers;

use App\Application\Services\Dto\Commands\CreateStudentCommand;
use App\Application\Services\Dto\Commands\DeleteStudentCommand;
use App\Application\Services\Dto\Commands\UpdateStudentCommand;
use App\Application\Services\Dto\Queries\GetStudentByIdQuery;
use App\Domain\Models\StudentModel;
use App\Domain\ValuesObjects\StudentId;
use App\Domain\ValuesObjects\StudentLastName;
use App\Domain\ValuesObjects\StudentName;


final class StudentApplicationMapper
{
    /**
     * Construye un StudentModel para CREAR.
     * Los Value Objects validan los datos: si algo es inválido, lanzan su excepción aquí.
     */
    public static function fromCreateCommandToModel(CreateStudentCommand $command): StudentModel
    {
        return new StudentModel(
            new StudentId($command->getId()),
            new StudentName($command->getName()),
            new StudentLastName($command->getLastName())
        );
    }

    /**
     * Construye un StudentModel para ACTUALIZAR.
     */
    public static function fromUpdateCommandToModel(UpdateStudentCommand $command): StudentModel
    {
        return new StudentModel(
            new StudentId($command->getId()),
            new StudentName($command->getName()),
            new StudentLastName($command->getLastName())
        );
    }

    /**
     * Extrae el StudentId desde una GetStudentByIdQuery.
     */
    public static function fromGetStudentByIdQueryToStudentId(GetStudentByIdQuery $query): StudentId
    {
        return new StudentId($query->getId());
    }

    /**
     * Extrae el StudentId desde un DeleteStudentCommand.
     */
    public static function fromDeleteCommandToStudentId(DeleteStudentCommand $command): StudentId
    {
        return new StudentId($command->getId());
    }

    /**
     * Convierte un StudentModel a array simple (para respuestas HTTP).
     *
     * @return array<string, string>
     */
    public static function fromModelToArray(StudentModel $Student): array
    {
        return [
            'id'       => $Student->id()->value(),
            'name'     => $Student->name()->value(),
            'lastName'    => $Student->lastName()->value()
        ];
    }

    /**
     * Convierte un array de StudentModel a array de arrays simples.
     *
     * @param  StudentModel[]              $Students
     * @return array<int, array<string, string>>
     */
    public static function fromModelsToArray(array $Students): array
    {
        $result = [];
        foreach ($Students as $Student) {
            $result[] = self::fromModelToArray($Student);
        }
        return $result;
    }
}