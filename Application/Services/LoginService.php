<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\LoginUseCase;
use App\Application\Ports\Out\GetUserByEmailPort;
use App\Application\Services\Dto\Commands\LoginCommand;
use App\Domain\Enums\UserStatusEnum;
use App\Domain\Exceptions\InvalidCredentialsException;
use App\Domain\Models\UserModel;
use App\Domain\ValuesObjects\UserEmail;

final class LoginService implements LoginUseCase
{
    private GetUserByEmailPort $getUserByEmailPort;

    public function __construct(GetUserByEmailPort $getUserByEmailPort)
    {
        $this->getUserByEmailPort = $getUserByEmailPort;
    }

    public function execute(LoginCommand $command): UserModel
    {
        $email = new UserEmail($command->getEmail());

        // 1. Busca el usuario por email y verifica la contraseña en un solo bloque
        //    (no se revela si el email existe o no → seguridad por oscuridad)
        $user = $this->getUserByEmailPort->getByEmail($email);
        if ($user === null || !$user->password()->verifyPlain($command->getPassword())) {
            throw InvalidCredentialsException::becauseCredentialsAreInvalid();
        }

        // 2. Verifica que la cuenta esté activa
        if ($user->status() !== UserStatusEnum::ACTIVE) {
            throw InvalidCredentialsException::becauseUserIsNotActive();
        }

        return $user;
    }
}