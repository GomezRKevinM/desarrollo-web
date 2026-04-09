<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers;

use App\Application\Ports\In\CreateStudentUseCase;
use App\Application\Ports\In\DeleteStudentUseCase;
use App\Application\Ports\In\GetAllStudentsUseCase;
use App\Application\Ports\In\GetByStudentIdUseCase;
use App\Application\Ports\In\UpdateStudentUseCase;
use App\Application\Services\Dto\Queries\GetAllStudentsQuery;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\StudentResponse;
use App\Infrastructure\Entrypoints\Web\Controllers\Mapper\StudentWebMapper;

final class StudentController
{
    public function __construct(
        private readonly CreateStudentUseCase  $createStudentUseCase,
        private readonly UpdateStudentUseCase  $updateStudentUseCase,
        private readonly GetByStudentIdUseCase $getStudentByIdUseCase,
        private readonly GetAllStudentsUseCase $getAllStudentsUseCase,
        private readonly DeleteStudentUseCase  $deleteStudentUseCase,
        private readonly StudentWebMapper      $mapper,
    ) {}

    /** @return StudentResponse[] */
    public function index(): array
    {
        $Students = $this->getAllStudentsUseCase->execute(new GetAllStudentsQuery());
        return $this->mapper->fromModelsToResponses($Students);
    }

    public function show(string $id): StudentResponse
    {
        $query = $this->mapper->fromIdToGetByIdQuery($id);
        $Student  = $this->getStudentByIdUseCase->execute($query);
        return $this->mapper->fromModelToResponse($Student);
    }

    public function store(CreateStudentWebRequest $request): StudentResponse
    {
        $command = $this->mapper->fromCreateRequestToCommand($request);
        $Student    = $this->createStudentUseCase->execute($command);
        return $this->mapper->fromModelToResponse($Student);
    }

    public function update(UpdateStudentWebRequest $request): StudentResponse
    {
        $command = $this->mapper->fromUpdateRequestToCommand($request);
        $Student    = $this->updateStudentUseCase->execute($command);
        return $this->mapper->fromModelToResponse($Student);
    }

    public function delete(string $id): void
    {
        $command = $this->mapper->fromIdToDeleteCommand($id);
        $this->deleteStudentUseCase->execute($command);
    }

}