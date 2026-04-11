<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\UpdateCalificationUseCase;
use App\Application\Ports\Out\GetCalificationByIdPort;
use App\Application\Ports\Out\UpdateCalificationPort;
use App\Application\Services\Dto\Commands\UpdateCalificationCommand;
use App\Domain\Exceptions\CalificationNotFoundException;
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
use App\Domain\ValuesObjects\UserId;

final class UpdateCalificationService implements UpdateCalificationUseCase
{
    private UpdateCalificationPort $updateCalificationPort;
    private GetCalificationByIdPort $getCalificationByIdPort;

    public function __construct(
        UpdateCalificationPort $updateCalificationPort,
        GetCalificationByIdPort $getCalificationByIdPort
    ) {
        $this->updateCalificationPort = $updateCalificationPort;
        $this->getCalificationByIdPort = $getCalificationByIdPort;
    }

    public function execute(UpdateCalificationCommand $command): CalificationModel
    {
        // 1. Verifica que la calificación existe
        $calificationId = new CalificationId($command->getId());
        $currentCalification = $this->getCalificationByIdPort->getById($calificationId);
        if ($currentCalification === null) {
            throw CalificationNotFoundException::becauseIdWasNotFound($calificationId->value());
        }

        // 2. Construye el CalificationModel actualizado
        $calificationToUpdate = new CalificationModel(
            $calificationId,
            new CalificationFecha($command->getFecha()),
            new CalificationDocente($command->getDocente()),
            new CalificationAsignatura($command->getAsignatura()),
            new CalificationCarrera($command->getCarrera()),
            new CalificationUniversidad($command->getUniversidad()),
            new CalificationPeriodo($command->getPeriodo()),
            new CalificationActividadEvaluada($command->getActividadEvaluada()),
            new CalificationPorcentaje($command->getPorcentaje()),
            new UserId($command->getStudentId()),
            new CalificationNota($command->getNota())
        );

        // 3. Persiste y retorna
        return $this->updateCalificationPort->update($calificationToUpdate);
    }
}

