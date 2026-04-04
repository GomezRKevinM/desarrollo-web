<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Commands\CreateUserCommand;
use App\crud_usuarios\Domain\Models\UserModel;

interface CreateUserUseCase
{
    public function execute(CreateUserCommand $command): UserModel;
}