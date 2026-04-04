<?php

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Commands\LoginCommand;
use App\crud_usuarios\Domain\Models\UserModel;

interface LoginUseCase
{
    public function execute(LoginCommand $command): UserModel;
}