<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Entity;

final class StudentEntity
{
    private string $id;
    private string $name;
    private string $lastName;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $name,
        string $lastName,
        ?string $createdAt = null,
        ?string $updatedAt = null
    )
    {
        $this->id = trim($id);
        $this->name = trim($name);
        $this->lastName = trim($lastName);
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function id(): string { return $this->id; }
    public function name(): string { return $this->name; }
    public function lastName(): string { return $this->lastName; }
    public function createdAt(): ?string { return $this->createdAt; }
    public function updatedAt(): ?string { return $this->updatedAt; }
}