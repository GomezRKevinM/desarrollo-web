<?php

declare(strict_types=1);

namespace App\crud_usuarios\Domain\Events;

use App\crud_usuarios\Domain\Models\UserModel;


class UserUpdatedDomainEvent extends DomainEvent
{
    private UserModel $user;

    public function __construct(UserModel $user)
    {
        parent::__construct('user.updated');
        $this->user = $user;
    }

    public function user() : UserModel
    {
        return $this->user;
    }

    public function payload() : array
    {
        return array(
            'id' => $this->user->id()->value(),
            'name' => $this->user->name()->value(),
            'email' => $this->user->email()->value(),
            'role' => $this->user->role(),
            'status' => $this->user->status()
        );
    }
}