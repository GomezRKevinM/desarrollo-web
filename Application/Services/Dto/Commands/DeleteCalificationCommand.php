<?php

declare(strict_types=1);

namespace App\Application\Services\Dto\Commands;

final class DeleteCalificationCommand
{
    private string $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
    }

    public function getId(): string { return $this->id; }
}

