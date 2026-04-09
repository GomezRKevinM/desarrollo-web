<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Dto;

final class StudentPersistenceDto
{
    private string $id;
    private string $name;
    private string $lastName;

    public function __construct(
        string $id,
        string $name,
        string $lastName
    )
    {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->lastName = trim($lastName);
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function lastName(): string { return $this->lastName; }
}