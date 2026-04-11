<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\DeleteCalificationCommand;

interface DeleteCalificationUseCase
{
    public function execute(DeleteCalificationCommand $command): void;
}

