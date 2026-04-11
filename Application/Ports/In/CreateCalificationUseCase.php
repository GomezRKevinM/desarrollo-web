<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\CreateCalificationCommand;
use App\Domain\Models\CalificationModel;

interface CreateCalificationUseCase
{
    public function execute(CreateCalificationCommand $command): CalificationModel;
}

