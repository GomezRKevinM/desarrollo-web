<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Commands\UpdateUserCommand;
use App\crud_usuarios\Domain\Models\UserModel;

interface UpdateUserUseCase
{
    public function execute(UpdateUserCommand $command): UserModel;
}