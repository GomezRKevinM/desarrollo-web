<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetAllCalificationsQuery;
use App\Domain\Models\CalificationModel;

interface GetAllCalificationsUseCase
{
    /** @return CalificationModel[] */
    public function execute(GetAllCalificationsQuery $query): array;
}

