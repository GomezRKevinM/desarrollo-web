<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\In;

use App\crud_usuarios\Application\Services\Dto\Commands\DeleteUserCommand;

interface DeleteUserCase
{
    public function execute(DeleteUserCommand $command): void;
}