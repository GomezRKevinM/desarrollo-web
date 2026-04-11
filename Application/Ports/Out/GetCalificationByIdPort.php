<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\CalificationModel;
use App\Domain\ValuesObjects\CalificationId;

interface GetCalificationByIdPort
{
    public function getById(CalificationId $id): ?CalificationModel;
}

