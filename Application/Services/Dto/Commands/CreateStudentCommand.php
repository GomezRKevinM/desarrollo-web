<?php

declare(strict_types=1);

namespace App\Application\Services\Dto\Commands;

final class CreateStudentCommand
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

    public function getId(): string { return $this->id; }

    public function getName(): string { return $this->name; }

    public function getLastName(): string { return $this->lastName; }
}