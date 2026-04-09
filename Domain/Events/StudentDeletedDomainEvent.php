<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\ValuesObjects\StudentId;


class StudentDeletedDomainEvent extends DomainEvent
{

    private StudentId $studentId;

    public function __construct(StudentId $studentId)
    {
        parent::__construct('student.deleted');
        $this->studentId = $studentId;
    }

    public function StudentId() : StudentId
    {
        return $this->studentId;
    }

    public function payload() : array
    {
        return array(
            'id' => $this->studentId->value()
        );
    }
}