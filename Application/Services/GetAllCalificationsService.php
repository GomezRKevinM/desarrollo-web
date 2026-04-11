<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetAllCalificationsUseCase;
use App\Application\Ports\Out\GetAllCalificationsPort;
use App\Application\Services\Dto\Queries\GetAllCalificationsQuery;
use App\Domain\Models\CalificationModel;

final class GetAllCalificationsService implements GetAllCalificationsUseCase
{
    private GetAllCalificationsPort $getAllCalificationsPort;

    public function __construct(GetAllCalificationsPort $getAllCalificationsPort)
    {
        $this->getAllCalificationsPort = $getAllCalificationsPort;
    }

    /** @return CalificationModel[] */
    public function execute(GetAllCalificationsQuery $query): array
    {
        return $this->getAllCalificationsPort->getAll();
    }
}

