<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Dto;

final readonly class UserResponse
{
    public function __construct(
        private string $id,
        private string $name,
        private string $email,
        private string $role,
        private string $status,
    ) {}

    public function getId(): string     { return $this->id; }
    public function getName(): string   { return $this->name; }
    public function getEmail(): string  { return $this->email; }
    public function getRole(): string   { return $this->role; }
    public function getStatus(): string { return $this->status; }

    /** @return array<string, string> */
    public function toArray(): array
    {
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'email'  => $this->email,
            'role'   => $this->role,
            'status' => $this->status,
        ];
    }
}