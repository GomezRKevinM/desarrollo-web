<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Events;

abstract class DomainEvent
{
    private string $eventName;
    private string $occurredOn;

    public function __construct(string $eventName)
    {
        $this->eventName  = $eventName;
        $this->occurredOn = date('Y-m-d H:i:s');
    }

    public function eventName(): string
    {
        return $this->eventName;
    }

    public function occurredOn(): string
    {
        return $this->occurredOn;
    }

    abstract public function payload(): array;
}