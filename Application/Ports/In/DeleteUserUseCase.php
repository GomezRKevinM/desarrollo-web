<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\DeleteUserCommand;

interface DeleteUserUseCase
{
    public function execute(DeleteUserCommand $command): void;
}