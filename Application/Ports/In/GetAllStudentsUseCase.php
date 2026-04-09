<?php

declare(strict_types = 1);

namespace App\Application\Ports\In;

use App\Application\Services\Dto\Queries\GetAllStudentsQuery;
use App\Domain\Models\StudentModel;

interface GetAllStudentsUseCase
{
    /** @return StudentModel[] */
    public function execute(GetAllStudentsQuery $query): array;
}