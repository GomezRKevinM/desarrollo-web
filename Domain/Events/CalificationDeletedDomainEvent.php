<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\ValuesObjects\CalificationId;

class CalificationDeletedDomainEvent extends DomainEvent
{
    public function __construct(private readonly CalificationId $calificacionId)
    {
        parent::__construct('calificacion.deleted');
    }

    public function calificacionId(): CalificationId { return $this->calificacionId; }

    public function payload(): array
    {
        return ['id' => $this->calificacionId->value()];
    }

}