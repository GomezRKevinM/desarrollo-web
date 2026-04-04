<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Ports\Out;

use App\crud_usuarios\Domain\ValuesObjects\UserId;

interface DeleteUserPort
{
    public function delete(UserId $userId): void;
}