<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\CreateCalificationUseCase;
use App\Application\Ports\Out\SaveCalificationPort;
use App\Application\Services\Mappers\CalificationApplicationMapper;
use App\Domain\Models\CalificationModel;
use App\Application\Services\Dto\Commands\CreateCalificationCommand;

final class CreateCalificationService implements CreateCalificationUseCase
{
    private SaveCalificationPort $saveCalificationPort;

    public function __construct(
        SaveCalificationPort $saveCalificationPort
    ) {
        $this->saveCalificationPort = $saveCalificationPort;
    }

    public function execute(CreateCalificationCommand $command): CalificationModel
    {
        // 1. Construye el CalificationModel completo (los VOs validan cada campo)
        $calification = CalificationApplicationMapper::fromCreateCommandToModel($command);

        // 2. Persiste y retorna la calificación guardada
        return $this->saveCalificationPort->save($calification);
    }
}

