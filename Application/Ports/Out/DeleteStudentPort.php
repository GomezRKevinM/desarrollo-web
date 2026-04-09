<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\ValuesObjects\StudentId;

interface DeleteStudentPort
{
    public function delete(StudentId $StudentId): void;
}