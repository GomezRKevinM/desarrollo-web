<?php

declare(strict_types=1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetStudentByIdQuery;
use App\Domain\Models\StudentModel;

interface GetByStudentIdUseCase
{
    public function execute(GetStudentByIdQuery $query): ?StudentModel;
}