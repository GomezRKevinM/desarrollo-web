<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\StudentModel;

interface SaveStudentPort
{
    public function save(StudentModel $Student): StudentModel;
}