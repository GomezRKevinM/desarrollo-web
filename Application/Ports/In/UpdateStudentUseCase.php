<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\UpdateStudentCommand;
use App\Domain\Models\StudentModel;

interface UpdateStudentUseCase
{
    public function execute(UpdateStudentCommand $command): StudentModel;
}