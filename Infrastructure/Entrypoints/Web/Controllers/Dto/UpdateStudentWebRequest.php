<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Dto;

final readonly class UpdateStudentWebRequest
{
    public function __construct(
        private string $id,
        private string $name,
        private string $lastName
    ) {}

    public function getId(): string       { return $this->id; }
    public function getName(): string     { return $this->name; }
    public function getLastName(): string    { return $this->lastName; }
}