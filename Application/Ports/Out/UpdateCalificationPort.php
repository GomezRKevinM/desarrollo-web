<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\CalificationModel;
use App\Domain\ValuesObjects\CalificationId;

interface UpdateCalificationPort
{
    public function update(CalificationModel $calification): CalificationModel;
}

