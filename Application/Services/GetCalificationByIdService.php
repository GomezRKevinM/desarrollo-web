<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetByCalificationIdUseCase;
use App\Application\Ports\Out\GetCalificationByIdPort;
use App\Application\Services\Dto\Queries\GetCalificationByIdQuery;
use App\Application\Services\Mappers\CalificationApplicationMapper;
use App\Domain\Exceptions\CalificationNotFoundException;
use App\Domain\Models\CalificationModel;

final class GetCalificationByIdService implements GetByCalificationIdUseCase
{
    private GetCalificationByIdPort $getCalificationByIdPort;

    public function __construct(GetCalificationByIdPort $getCalificationByIdPort)
    {
        $this->getCalificationByIdPort = $getCalificationByIdPort;
    }

    public function execute(GetCalificationByIdQuery $query): CalificationModel
    {
        // 1. Convierte la Query a CalificationId
        $calificationId = CalificationApplicationMapper::fromGetCalificationByIdQueryToCalificationId($query);

        // 2. Busca la calificación
        $calification = $this->getCalificationByIdPort->getById($calificationId);
        if ($calification === null) {
            throw CalificationNotFoundException::becauseIdWasNotFound($calificationId->value());
        }

        return $calification;
    }
}

