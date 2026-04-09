<?php

namespace App\Application\Ports\Out;

use App\Domain\Models\StudentModel;

interface GetAllStudentsPort
{
    /** @return StudentModel[] */
    public function getAll(): array;
}