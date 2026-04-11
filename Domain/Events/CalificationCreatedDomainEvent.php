<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\Models\CalificationModel;

final class CalificationCreatedDomainEvent extends DomainEvent
{

    public function __construct(private readonly CalificationModel $calification)
    {
        parent::__construct('calification.created');
    }

    public function calification(): CalificationModel { return $this->calification; }

    public function payload(): array
    {
        return [
            'id'                => $this->calification->id()->value(),
            'studentId'         => $this->calification->studentId()->value(),
            'asignatura'        => $this->calification->asignatura()->value(),
            'nota'              => $this->calification->nota()->value(),
            'porcentaje'        => $this->calification->porcentaje()->value(),
            'actividadEvaluada' => $this->calification->actividadEvaluada()->value(),
        ];
    }

}