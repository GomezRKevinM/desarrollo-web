<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\StudentModel;

class StudentCreatedDomainEvent extends DomainEvent
{

    private StudentModel $student;

    public function __construct(StudentModel $student)
    {
        parent::__construct('student.created');
        $this->student = $student;
    }

    public function student() : StudentModel
    {
        return $this->student;
    }

    public function payload(): array
    {
        return array(
            'id' => $this->student->id()->value(),
            'name' => $this->student->name()->value(),
            'lastName' => $this->student->lastName()->value(),
        );
    }
}