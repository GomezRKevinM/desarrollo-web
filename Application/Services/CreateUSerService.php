<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\CreateUserUseCase;
use App\Application\Ports\Out\GetUserByEmailPort;
use App\Application\Ports\Out\SaveUserPort;
use App\Application\Services\Mappers\UserApplicationMapper;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\ValuesObjects\UserEmail;
use App\Domain\Models\UserModel;
use App\Application\Services\Dto\Commands\CreateUserCommand;

final class CreateUserService implements CreateUserUseCase
{
    private SaveUserPort $saveUserPort;
    private GetUserByEmailPort $getUserByEmailPort;

    public function __construct(
        SaveUserPort $saveUserPort,
        GetUserByEmailPort $getUserByEmailPort
    ) {
        $this->saveUserPort         = $saveUserPort;
        $this->getUserByEmailPort   = $getUserByEmailPort;
    }

    public function execute(CreateUserCommand $command): UserModel
    {
        // 1. Construye UserEmail → si es inválido, UserEmail lanza InvalidUserEmailException
        $email = new UserEmail($command->getEmail());

        // 2. Verifica que el email no esté ya registrado
        $existingUser = $this->getUserByEmailPort->getByEmail($email);
        if ($existingUser !== null) {
            throw UserAlreadyExistsException::becauseEmailAlreadyExists($email->value());
        }

        // 3. Construye el UserModel completo (los VOs validan cada campo)
        $user = UserApplicationMapper::fromCreateCommandToModel($command);

        // 4. Persiste y retorna el usuario guardado
        return $this->saveUserPort->save($user);
    }
}
