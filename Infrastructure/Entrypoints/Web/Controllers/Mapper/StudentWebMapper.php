<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Mapper;

use App\Application\Services\Dto\Commands\CreateStudentCommand;
use App\Application\Services\Dto\Commands\DeleteStudentCommand;
use App\Application\Services\Dto\Commands\UpdateStudentCommand;
use App\Application\Services\Dto\Queries\GetStudentByIdQuery;
use App\Domain\Models\StudentModel;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateStudentWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\StudentResponse;

final class StudentWebMapper
{
    public function fromCreateRequestToCommand(CreateStudentWebRequest $request): CreateStudentCommand
    {
        return new CreateStudentCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            lastName:    $request->getLastName()
        );
    }

    public function fromUpdateRequestToCommand(UpdateStudentWebRequest $request): UpdateStudentCommand
    {
        return new UpdateStudentCommand(
            id:       $request->getId(),
            name:     $request->getName(),
            lastName:    $request->getLastName()
        );
    }

    public function fromIdToGetByIdQuery(string $id): GetStudentByIdQuery
    {
        return new GetStudentByIdQuery($id);
    }

    public function fromIdToDeleteCommand(string $id): DeleteStudentCommand
    {
        return new DeleteStudentCommand($id);
    }

    public function fromModelToResponse(StudentModel $Student): StudentResponse
    {
        return new StudentResponse(
            id:     $Student->id()->value(),
            name:   $Student->name()->value(),
            lastName:  $Student->lastName()->value()
        );
    }

    /**
     * @param  StudentModel[]   $Students
     * @return StudentResponse[]
     */
    public function fromModelsToResponses(array $Students): array
    {
        return array_map(
            fn(StudentModel $Student): StudentResponse => $this->fromModelToResponse($Student),
            $Students,
        );
    }

}