<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetByStudentIdUseCase;
use App\Application\Ports\Out\GetStudentByIdPort;
use App\Application\Services\Dto\Queries\GetStudentByIdQuery;
use App\Application\Services\Mappers\StudentApplicationMapper;
use App\Domain\Exceptions\StudentNotFoundException;
use App\Domain\Models\StudentModel;

final class GetStudentByIdService implements GetByStudentIdUseCase
{
    private GetStudentByIdPort $getStudentByIdPort;

    public function __construct(GetStudentByIdPort $getStudentByIdPort)
    {
        $this->getStudentByIdPort = $getStudentByIdPort;
    }

    public function execute(GetStudentByIdQuery $query): StudentModel
    {
        // 1. Convierte la Query a StudentId
        $StudentId = StudentApplicationMapper::fromGetStudentByIdQueryToStudentId($query);

        // 2. Busca el usuario
        $Student = $this->getStudentByIdPort->getById($StudentId);
        if ($Student === null) {
            throw StudentNotFoundException::becauseIdWasNotFound($StudentId->value());
        }

        return $Student;
    }
}