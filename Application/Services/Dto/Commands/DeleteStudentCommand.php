<?php

namespace App\Application\Services\Dto\Commands;

final class DeleteStudentCommand
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
    }

    public function getId(): string { return $this->id; }
}