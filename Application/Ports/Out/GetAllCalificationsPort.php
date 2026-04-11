<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\CalificationModel;

interface GetAllCalificationsPort
{
    /** @return CalificationModel[] */
    public function getAll(): array;
}

