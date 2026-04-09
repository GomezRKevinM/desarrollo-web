<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\DeleteStudentCommand;

interface DeleteStudentUseCase
{
    public function execute(DeleteStudentCommand $command): void;
}