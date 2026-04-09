<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\CreateStudentUseCase;
use App\Application\Ports\Out\SaveStudentPort;
use App\Application\Services\Mappers\StudentApplicationMapper;
use App\Domain\Models\StudentModel;
use App\Application\Services\Dto\Commands\CreateStudentCommand;

final class CreateStudentService implements CreateStudentUseCase
{
    private SaveStudentPort $saveStudentPort;

    public function __construct(
        SaveStudentPort $saveStudentPort
    ) {
        $this->saveStudentPort = $saveStudentPort;
    }

    public function execute(CreateStudentCommand $command): StudentModel
    {
        // 1. Construye el StudentModel completo (los VOs validan cada campo)
        $Student = StudentApplicationMapper::fromCreateCommandToModel($command);

        // 2. Persiste y retorna el usuario guardado
        return $this->saveStudentPort->save($Student);
    }
}
