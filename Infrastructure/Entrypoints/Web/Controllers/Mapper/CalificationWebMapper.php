<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Mapper;

use App\Application\Services\Dto\Commands\CreateCalificationCommand;
use App\Application\Services\Dto\Commands\DeleteCalificationCommand;
use App\Application\Services\Dto\Commands\UpdateCalificationCommand;
use App\Application\Services\Dto\Queries\GetCalificationByIdQuery;
use App\Domain\Models\CalificationModel;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateCalificationWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateCalificationWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CalificationResponse;

final class CalificationWebMapper
{
    public function fromCreateRequestToCommand(CreateCalificationWebRequest $request): CreateCalificationCommand
    {
        return new CreateCalificationCommand(
            id:                $request->getId(),
            fecha:             $request->getFecha(),
            docente:           $request->getDocente(),
            asignatura:        $request->getAsignatura(),
            carrera:           $request->getCarrera(),
            universidad:       $request->getUniversidad(),
            periodo:           $request->getPeriodo(),
            actividadEvaluada: $request->getActividadEvaluada(),
            porcentaje:        $request->getPorcentaje(),
            studentId:         $request->getStudentId(),
            nota:              $request->getNota(),
        );
    }

    public function fromUpdateRequestToCommand(UpdateCalificationWebRequest $request): UpdateCalificationCommand
    {
        return new UpdateCalificationCommand(
            id:                $request->getId(),
            fecha:             $request->getFecha(),
            docente:           $request->getDocente(),
            asignatura:        $request->getAsignatura(),
            carrera:           $request->getCarrera(),
            universidad:       $request->getUniversidad(),
            periodo:           $request->getPeriodo(),
            actividadEvaluada: $request->getActividadEvaluada(),
            porcentaje:        $request->getPorcentaje(),
            studentId:         $request->getStudentId(),
            nota:              $request->getNota(),
        );
    }

    public function fromIdToGetByIdQuery(string $id): GetCalificationByIdQuery
    {
        return new GetCalificationByIdQuery($id);
    }

    public function fromIdToDeleteCommand(string $id): DeleteCalificationCommand
    {
        return new DeleteCalificationCommand($id);
    }

    public function fromModelToResponse(CalificationModel $calification): CalificationResponse
    {
        return new CalificationResponse(
            id:                $calification->id()->value(),
            fecha:             $calification->fecha()->toDbFormat(),
            docente:           $calification->docente()->value(),
            asignatura:        $calification->asignatura()->value(),
            carrera:           $calification->carrera()->value(),
            universidad:       $calification->universidad()->value(),
            periodo:           $calification->periodo()->value(),
            actividadEvaluada: $calification->actividadEvaluada()->value(),
            porcentaje:        $calification->porcentaje()->value(),
            studentId:         $calification->studentId()->value(),
            nota:              $calification->nota()->value(),
        );
    }

    /**
     * @param  CalificationModel[]   $califications
     * @return CalificationResponse[]
     */
    public function fromModelsToResponses(array $califications): array
    {
        return array_map(
            fn(CalificationModel $calification): CalificationResponse => $this->fromModelToResponse($calification),
            $califications,
        );
    }
}