<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Commands\CreateStudentCommand;
use App\Domain\Models\StudentModel;

interface CreateStudentUseCase
{
    public function execute(CreateStudentCommand $command): StudentModel;
}