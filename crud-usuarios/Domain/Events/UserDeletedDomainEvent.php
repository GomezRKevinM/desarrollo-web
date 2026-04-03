<?php

declare(strict_types=1);

namespace App\Domain\Events;

use App\Domain\ValuesObjects\UserId;


class UserDeletedDomainEvent extends DomainEvent
{

    private UserId $userId;

    public function __construct(UserId $userId)
    {
        parent::__construct('user.deleted');
        $this->userId = $userId;
    }

    public function userId() : UserId
    {
        return $this->userId;
    }

    public function payload() : array
    {
        return array(
            'id' => $this->userId->value()
        );
    }
}