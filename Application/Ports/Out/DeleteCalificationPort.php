<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\ValuesObjects\CalificationId;

interface DeleteCalificationPort
{
    public function delete(CalificationId $id): void;
}

