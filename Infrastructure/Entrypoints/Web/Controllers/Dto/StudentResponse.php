<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Dto;

final readonly class StudentResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private string $lastName
    ) {}

    public function getId(): string     { return $this->id; }
    public function getName(): string   { return $this->name; }
    public function getLastName(): string  { return $this->lastName; }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'lastName'  => $this->lastName
        ];
    }
}