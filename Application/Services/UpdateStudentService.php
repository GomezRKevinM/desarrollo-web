<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\UpdateStudentUseCase;
use App\Application\Ports\Out\GetStudentByIdPort;
use App\Application\Ports\Out\UpdateStudentPort;
use App\Application\Services\Dto\Commands\UpdateStudentCommand;
use App\Domain\Exceptions\StudentAlreadyExistsException;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Models\StudentModel;
use App\Domain\ValuesObjects\StudentId;
use App\Domain\ValuesObjects\StudentLastName;
use App\Domain\ValuesObjects\StudentName;

final class UpdateStudentService implements UpdateStudentUseCase
{
    private UpdateStudentPort $updateStudentPort;
    private GetStudentByIdPort $getStudentByIdPort;

    public function __construct(
        UpdateStudentPort $updateStudentPort,
        GetStudentByIdPort $getStudentByIdPort
    ) {
        $this->updateStudentPort       = $updateStudentPort;
        $this->getStudentByIdPort      = $getStudentByIdPort;
    }

    public function execute(UpdateStudentCommand $command): StudentModel
    {
        // 1. Verifica que el usuario existe
        $StudentId      = new StudentId($command->getId());
        $currentStudent = $this->getStudentByIdPort->getById($StudentId);
        if ($currentStudent === null) {
            throw StudentNotFoundException::becauseIdWasNotFound($StudentId->value());
        }

        // 2. Construye el StudentModel actualizado
        $StudentToUpdate = new StudentModel(
            $StudentId,
            new StudentName($command->getName()),
            new StudentLastName($command->getLastName())
        );

        // 3. Persiste y retorna
        return $this->updateStudentPort->update($StudentToUpdate);
    }
}