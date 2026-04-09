<?php

declare(strict_types=1);

namespace App\Domain\Models;

use App\Domain\ValuesObjects\StudentId;
use App\Domain\ValuesObjects\StudentLastName;
use App\Domain\ValuesObjects\StudentName;

final class StudentModel
{
    private StudentId $id;
    private StudentName $name;
    private StudentLastName $lastName;

    public function __construct(
        StudentId $id,
        StudentName $name,
        StudentLastName $lastName
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->lastName = $lastName;
    }

    // Getters

    public function id(): StudentId
    {
        return $this->id;
    }

    public function name(): StudentName
    {
        return $this->name;
    }

    public function lastName(): StudentLastName
    {
        return $this->lastName;
    }
}