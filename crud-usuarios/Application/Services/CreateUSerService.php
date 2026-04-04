<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Services;

use App\crud_usuarios\Application\Ports\In\CreateUserUseCase;
use App\crud_usuarios\Application\Ports\Out\GetUserByEmailPort;
use App\crud_usuarios\Application\Ports\Out\SaveUserPort;
use App\crud_usuarios\Application\Services\Mappers\UserApplicationMapper;
use App\crud_usuarios\Domain\Exceptions\UserAlreadyExistsException;
use App\crud_usuarios\Domain\ValuesObjects\UserEmail;
use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Application\Services\Dto\Commands\CreateUserCommand;

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
