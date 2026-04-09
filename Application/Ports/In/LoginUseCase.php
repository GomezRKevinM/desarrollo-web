<?php

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\LoginCommand;
use App\Domain\Models\UserModel;

interface LoginUseCase
{
    public function execute(LoginCommand $command): UserModel;
}