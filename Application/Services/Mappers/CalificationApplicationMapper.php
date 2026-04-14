<?php

declare(strict_types=1);

namespace App\Application\Services\Mappers;

use App\Application\Services\Dto\Commands\CreateCalificationCommand;
use App\Application\Services\Dto\Commands\DeleteCalificationCommand;
use App\Application\Services\Dto\Commands\UpdateCalificationCommand;
use App\Application\Services\Dto\Queries\GetCalificationByIdQuery;
use App\Domain\Models\CalificationModel;
use App\Domain\ValuesObjects\CalificationId;
use App\Domain\ValuesObjects\CalificationFecha;
use App\Domain\ValuesObjects\CalificationDocente;
use App\Domain\ValuesObjects\CalificationAsignatura;
use App\Domain\ValuesObjects\CalificationCarrera;
use App\Domain\ValuesObjects\CalificationUniversidad;
use App\Domain\ValuesObjects\CalificationPeriodo;
use App\Domain\ValuesObjects\CalificationActividadEvaluada;
use App\Domain\ValuesObjects\CalificationPorcentaje;
use App\Domain\ValuesObjects\CalificationNota;
use App\Domain\ValuesObjects\StudentId;

final class CalificationApplicationMapper
{
    /**
     * Construye un CalificationModel para CREAR.
     * Los Value Objects validan los datos: si algo es inválido, lanzan su excepción aquí.
     */
    public static function fromCreateCommandToModel(CreateCalificationCommand $command): CalificationModel
    {
        return new CalificationModel(
            new CalificationId($command->getId()),
            new CalificationFecha($command->getFecha()),
            new CalificationDocente($command->getDocente()),
            new CalificationAsignatura($command->getAsignatura()),
            new CalificationCarrera($command->getCarrera()),
            new CalificationUniversidad($command->getUniversidad()),
            new CalificationPeriodo($command->getPeriodo()),
            new CalificationActividadEvaluada($command->getActividadEvaluada()),
            new CalificationPorcentaje($command->getPorcentaje()),
            new StudentId($command->getStudentId()),
            new CalificationNota($command->getNota())
        );
    }

    /**
     * Construye un CalificationModel para ACTUALIZAR.
     */
    public static function fromUpdateCommandToModel(UpdateCalificationCommand $command): CalificationModel
    {
        return new CalificationModel(
            new CalificationId($command->getId()),
            new CalificationFecha($command->getFecha()),
            new CalificationDocente($command->getDocente()),
            new CalificationAsignatura($command->getAsignatura()),
            new CalificationCarrera($command->getCarrera()),
            new CalificationUniversidad($command->getUniversidad()),
            new CalificationPeriodo($command->getPeriodo()),
            new CalificationActividadEvaluada($command->getActividadEvaluada()),
            new CalificationPorcentaje($command->getPorcentaje()),
            new StudentId($command->getStudentId()),
            new CalificationNota($command->getNota())
        );
    }

    /**
     * Extrae el CalificationId desde una GetCalificationByIdQuery.
     */
    public static function fromGetCalificationByIdQueryToCalificationId(GetCalificationByIdQuery $query): CalificationId
    {
        return new CalificationId($query->getId());
    }

    /**
     * Extrae el CalificationId desde un DeleteCalificationCommand.
     */
    public static function fromDeleteCommandToCalificationId(DeleteCalificationCommand $command): CalificationId
    {
        return new CalificationId($command->getId());
    }

    /**
     * Convierte un CalificationModel a array simple (para respuestas HTTP).
     *
     * @return array<string, string>
     */
    public static function fromModelToArray(CalificationModel $calification): array
    {
        return $calification->toArray();
    }

    /**
     * Convierte un array de CalificationModel a array de arrays simples.
     *
     * @param  CalificationModel[]              $califications
     * @return array<int, array<string, string>>
     */
    public static function fromModelsToArray(array $califications): array
    {
        $result = [];
        foreach ($califications as $calification) {
            $result[] = self::fromModelToArray($calification);
        }
        return $result;
    }
}

