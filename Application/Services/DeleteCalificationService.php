<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\DeleteCalificationUseCase;
use App\Application\Ports\Out\DeleteCalificationPort;
use App\Application\Ports\Out\GetCalificationByIdPort;
use App\Application\Services\Dto\Commands\DeleteCalificationCommand;
use App\Application\Services\Mappers\CalificationApplicationMapper;
use App\Domain\Exceptions\CalificationNotFoundException;

final class DeleteCalificationService implements DeleteCalificationUseCase
{
    private DeleteCalificationPort $deleteCalificationPort;
    private GetCalificationByIdPort $getCalificationByIdPort;

    public function __construct(
        DeleteCalificationPort $deleteCalificationPort,
        GetCalificationByIdPort $getCalificationByIdPort
    ) {
        $this->deleteCalificationPort = $deleteCalificationPort;
        $this->getCalificationByIdPort = $getCalificationByIdPort;
    }

    public function execute(DeleteCalificationCommand $command): void
    {
        // 1. Extrae el CalificationId del comando
        $calificationId = CalificationApplicationMapper::fromDeleteCommandToCalificationId($command);

        // 2. Verifica que la calificación existe antes de eliminar
        $existingCalification = $this->getCalificationByIdPort->getById($calificationId);
        if ($existingCalification === null) {
            throw CalificationNotFoundException::becauseIdWasNotFound($calificationId->value());
        }

        // 3. Elimina
        $this->deleteCalificationPort->delete($calificationId);
    }
}

