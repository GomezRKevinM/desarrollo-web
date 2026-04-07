<?php

declare(strict_types=1);

namespace App\crud_usuarios\Infrastructure\Adapters\Persistence\MySQL\Dto;

final readonly class CreateUserWebRequest
{
    public function __construct(
        private string $id,
        private string $name,
        private string $email,
        private string $password,
        private string $role,
    ) {}

    public function getId(): string       { return $this->id; }
    public function getName(): string     { return $this->name; }
    public function getEmail(): string    { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string     { return $this->role; }
}