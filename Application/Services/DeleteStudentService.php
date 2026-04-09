<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\DeleteStudentUseCase;
use App\Application\Ports\Out\DeleteStudentPort;
use App\Application\Ports\Out\GetStudentByIdPort;
use App\Application\Services\Dto\Commands\DeleteStudentCommand;
use App\Application\Services\Mappers\StudentApplicationMapper;
use App\Domain\Exceptions\StudentNotFoundException;

final class DeleteStudentService implements DeleteStudentUseCase
{
    private DeleteStudentPort $deleteStudentPort;
    private GetStudentByIdPort $getStudentByIdPort;

    public function __construct(
        DeleteStudentPort $deleteStudentPort,
        GetStudentByIdPort $getStudentByIdPort
    ) {
        $this->deleteStudentPort  = $deleteStudentPort;
        $this->getStudentByIdPort = $getStudentByIdPort;
    }

    public function execute(DeleteStudentCommand $command): void
    {
        // 1. Extrae el StudentId del comando
        $StudentId = StudentApplicationMapper::fromDeleteCommandToStudentId($command);

        // 2. Verifica que el usuario existe antes de eliminar
        $existingStudent = $this->getStudentByIdPort->getById($StudentId);
        if ($existingStudent === null) {
            throw StudentNotFoundException::becauseIdWasNotFound($StudentId->value());
        }

        // 3. Elimina
        $this->deleteStudentPort->delete($StudentId);
    }
}