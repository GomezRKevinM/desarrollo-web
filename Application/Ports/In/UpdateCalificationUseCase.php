<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\UpdateCalificationCommand;
use App\Domain\Models\CalificationModel;

interface UpdateCalificationUseCase
{
    public function execute(UpdateCalificationCommand $command): CalificationModel;
}

