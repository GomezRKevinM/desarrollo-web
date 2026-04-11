<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetCalificationByIdQuery;
use App\Domain\Models\CalificationModel;

interface GetByCalificationIdUseCase
{
    public function execute(GetCalificationByIdQuery $query): CalificationModel;
}

