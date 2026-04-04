<?php

namespace App\crud_usuarios\Application\Services\Dto\Commands;

final class DeleteUserCommand
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
    }

    public function getId(): string { return $this->id; }
}