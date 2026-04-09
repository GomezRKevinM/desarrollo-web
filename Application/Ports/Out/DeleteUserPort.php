<?php

declare(strict_types=1);

namespace App\Application\Ports\Out;

use App\Domain\ValuesObjects\UserId;

interface DeleteUserPort
{
    public function delete(UserId $userId): void;
}