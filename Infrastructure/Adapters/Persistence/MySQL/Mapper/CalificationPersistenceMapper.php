<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Mapper;

use App\Domain\Models\CalificationModel;
use App\Domain\ValuesObjects\CalificationActividadEvaluada;
use App\Domain\ValuesObjects\CalificationAsignatura;
use App\Domain\ValuesObjects\CalificationCarrera;
use App\Domain\ValuesObjects\CalificationDocente;
use App\Domain\ValuesObjects\CalificationFecha;
use App\Domain\ValuesObjects\CalificationId;
use App\Domain\ValuesObjects\CalificationNota;
use App\Domain\ValuesObjects\CalificationPeriodo;
use App\Domain\ValuesObjects\CalificationPorcentaje;
use App\Domain\ValuesObjects\CalificationUniversidad;
use App\Domain\ValuesObjects\StudentId;
use App\Infrastructure\Adapters\Persistence\MySQL\Dto\CalificationPersistenceDto;
use App\Infrastructure\Adapters\Persistence\MySQL\Entity\CalificationEntity;

final class CalificationPersistenceMapper
{
    public function fromModelToDto(CalificationModel $calification): CalificationPersistenceDto
    {
        return new CalificationPersistenceDto(
            $calification->id()->value(),
            $calification->fecha()->toDbFormat(),
            $calification->docente()->value(),
            $calification->asignatura()->value(),
            $calification->carrera()->value(),
            $calification->universidad()->value(),
            $calification->periodo()->value(),
            $calification->actividadEvaluada()->value(),
            $calification->porcentaje()->value(),
            $calification->studentId()->value(),
            $calification->nota()->value()
        );
    }

    public function fromDtoToEntity(CalificationPersistenceDto $dto): CalificationEntity
    {
        return new CalificationEntity(
            $dto->id(),
            $dto->fecha(),
            $dto->docente(),
            $dto->asignatura(),
            $dto->carrera(),
            $dto->universidad(),
            $dto->periodo(),
            $dto->actividadEvaluada(),
            $dto->porcentaje(),
            $dto->studentId(),
            $dto->nota()
        );
    }

    public function fromRowToEntity(array $row): CalificationEntity
    {
        return new CalificationEntity(
            (string) $row['id'],
            (string) $row['fecha'],
            (string) $row['docente'],
            (string) $row['asignatura'],
            (string) $row['carrera'],
            (string) $row['universidad'],
            (string) $row['periodo'],
            (string) $row['actividadEvaluada'],
            (float) $row['porcentaje'],
            (string) $row['student_id'],
            (float) $row['nota'],
            isset($row['created_at']) ? (string) $row['created_at'] : null,
            isset($row['updated_at']) ? (string) $row['updated_at'] : null
        );
    }

    public function fromEntityToModel(CalificationEntity $entity): CalificationModel
    {
        return new CalificationModel(
            new CalificationId($entity->id()),
            new CalificationFecha($entity->fecha()),
            new CalificationDocente($entity->docente()),
            new CalificationAsignatura($entity->asignatura()),
            new CalificationCarrera($entity->carrera()),
            new CalificationUniversidad($entity->universidad()),
            new CalificationPeriodo($entity->periodo()),
            new CalificationActividadEvaluada($entity->actividadEvaluada()),
            new CalificationPorcentaje($entity->porcentaje()),
            new StudentId($entity->studentId()),
            new CalificationNota($entity->nota())
        );
    }

    public function fromRowToModel(array $row): CalificationModel
    {
        return $this->fromEntityToModel(
            $this->fromRowToEntity($row)
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return CalificationModel[]
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

