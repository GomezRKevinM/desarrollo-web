<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Repository;

use App\Application\Ports\Out\DeleteCalificationPort;
use App\Application\Ports\Out\GetAllCalificationsPort;
use App\Application\Ports\Out\GetCalificationByIdPort;
use App\Application\Ports\Out\SaveCalificationPort;
use App\Application\Ports\Out\UpdateCalificationPort;
use App\Domain\Models\CalificationModel;
use App\Domain\ValuesObjects\CalificationId;
use App\Infrastructure\Adapters\Persistence\MySQL\Mapper\CalificationPersistenceMapper;
use PDO;
use RuntimeException;

final class CalificationRepositoryMysql implements
    SaveCalificationPort,
    UpdateCalificationPort,
    GetCalificationByIdPort,
    GetAllCalificationsPort,
    DeleteCalificationPort
{

    private PDO $pdo;
    private CalificationPersistenceMapper $mapper;

    public function __construct(PDO $pdo, CalificationPersistenceMapper $mapper)
    {
        $this->pdo = $pdo;
        $this->mapper = $mapper;
    }

    public function save(CalificationModel $calification): CalificationModel
    {
        $dto = $this->mapper->fromModelToDto($calification);
        $sql = <<<'SQL'
            INSERT INTO calificacion (
                id,
                fecha,
                docente,
                asignatura,
                carrera,
                universidad,
                periodo,
                actividadEvaluada,
                porcentaje,
                studentId,
                nota,
                created_at,
                updated_at
            ) VALUES (
                :id,
                :fecha,
                :docente,
                :asignatura,
                :carrera,
                :universidad,
                :periodo,
                :actividadEvaluada,
                :porcentaje,
                :studentId,
                :nota,
                NOW(),
                NOW()
            )
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id'                => $dto->id(),
            ':fecha'             => $dto->fecha(),
            ':docente'           => $dto->docente(),
            ':asignatura'        => $dto->asignatura(),
            ':carrera'           => $dto->carrera(),
            ':universidad'       => $dto->universidad(),
            ':periodo'           => $dto->periodo(),
            ':actividadEvaluada' => $dto->actividadEvaluada(),
            ':porcentaje'        => $dto->porcentaje(),
            ':studentId'         => $dto->studentId(),
            ':nota'              => $dto->nota(),
        ));
        $savedCalification = $this->getById(new CalificationId($dto->id()));
        if ($savedCalification === null) {
            throw new RuntimeException('The Calification could not be recovered after save.');
        }
        return $savedCalification;
    }

    public function update(CalificationModel $calification): CalificationModel
    {
        $dto = $this->mapper->fromModelToDto($calification);
        $sql = <<<'SQL'
            UPDATE calificacion
            SET fecha = :fecha,
            docente = :docente,
            asignatura = :asignatura,
            carrera = :carrera,
            universidad = :universidad,
            periodo = :periodo,
            actividadEvaluada = :actividadEvaluada,
            porcentaje = :porcentaje,
            studentId = :studentId,
            nota = :nota,
            updated_at = NOW()
            WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id'                => $dto->id(),
            ':fecha'             => $dto->fecha(),
            ':docente'           => $dto->docente(),
            ':asignatura'        => $dto->asignatura(),
            ':carrera'           => $dto->carrera(),
            ':universidad'       => $dto->universidad(),
            ':periodo'           => $dto->periodo(),
            ':actividadEvaluada' => $dto->actividadEvaluada(),
            ':porcentaje'        => $dto->porcentaje(),
            ':studentId'         => $dto->studentId(),
            ':nota'              => $dto->nota(),
        ));
        $updatedCalification = $this->getById(new CalificationId($dto->id()));
        if ($updatedCalification === null) {
            throw new RuntimeException('The Calification could not be recovered after update.');
        }
        return $updatedCalification;
    }

    public function getById(CalificationId $id): ?CalificationModel
    {
        $sql = <<<'SQL'
            SELECT
            id,
            fecha,
            docente,
            asignatura,
            carrera,
            universidad,
            periodo,
            actividadEvaluada,
            porcentaje,
            studentId,
            nota,
            created_at,
            updated_at
            FROM calificacion
            WHERE id = :id
            LIMIT 1
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $id->value(),
        ));
        $row = $statement->fetch();
        if ($row === false) {
            return null;
        }
        return $this->mapper->fromRowToModel($row);
    }

    /**
     * @return CalificationModel[]
     */
    public function getAll(): array
    {
        $sql = <<<'SQL'
            SELECT
            id,
            fecha,
            docente,
            asignatura,
            carrera,
            universidad,
            periodo,
            actividadEvaluada,
            porcentaje,
            studentId,
            nota,
            created_at,
            updated_at
            FROM calificacion
            ORDER BY fecha DESC
        SQL;

        $statement = $this->pdo->query($sql);
        $rows = $statement->fetchAll();
        return $this->mapper->fromRowsToModels($rows);
    }

    public function delete(CalificationId $id): void
    {
        $sql = <<<'SQL'
            DELETE FROM calificacion WHERE id = :id
        SQL;

        $statement = $this->pdo->prepare($sql);
        $statement->execute(array(
            ':id' => $id->value(),
        ));
    }

}