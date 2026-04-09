<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\Models\StudentModel;
use App\Domain\ValuesObjects\StudentId;

interface GetStudentByIdPort
{
    // Retorna null si no existe (no lanza excepción)
    public function getById(StudentId $StudentId): ?StudentModel;
}