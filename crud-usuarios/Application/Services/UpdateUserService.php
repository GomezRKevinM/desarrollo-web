<?php

declare(strict_types=1);

namespace App\crud_usuarios\Application\Services;

use App\crud_usuarios\Application\Ports\In\UpdateUserUseCase;
use App\crud_usuarios\Application\Ports\Out\GetUserByEmailPort;
use App\crud_usuarios\Application\Ports\Out\GetUserByIdPort;
use App\crud_usuarios\Application\Ports\Out\UpdateUserPort;
use App\crud_usuarios\Application\Services\Dto\Commands\UpdateUserCommand;
use App\crud_usuarios\Domain\Exceptions\UserAlreadyExistsException;
use App\crud_usuarios\Domain\Exceptions\UserNotFoundException;
use App\crud_usuarios\Domain\Models\UserModel;
use App\crud_usuarios\Domain\ValuesObjects\UserEmail;
use App\crud_usuarios\Domain\ValuesObjects\UserId;
use App\crud_usuarios\Domain\ValuesObjects\UserName;
use App\crud_usuarios\Domain\ValuesObjects\UserPassword;

final class UpdateUserService implements UpdateUserUseCase
{
    private UpdateUserPort $updateUserPort;
    private GetUserByIdPort $getUserByIdPort;
    private GetUserByEmailPort $getUserByEmailPort;

    public function __construct(
        UpdateUserPort $updateUserPort,
        GetUserByIdPort $getUserByIdPort,
        GetUserByEmailPort $getUserByEmailPort
    ) {
        $this->updateUserPort       = $updateUserPort;
        $this->getUserByIdPort      = $getUserByIdPort;
        $this->getUserByEmailPort   = $getUserByEmailPort;
    }

    public function execute(UpdateUserCommand $command): UserModel
    {
        // 1. Verifica que el usuario existe
        $userId      = new UserId($command->getId());
        $currentUser = $this->getUserByIdPort->getById($userId);
        if ($currentUser === null) {
            throw UserNotFoundException::becauseIdWasNotFound($userId->value());
        }

        // 2. Verifica que el nuevo email no lo usa OTRO usuario
        $newEmail         = new UserEmail($command->getEmail());
        $userWithSameEmail = $this->getUserByEmailPort->getByEmail($newEmail);
        if ($userWithSameEmail !== null && !$userWithSameEmail->id()->equals($userId)) {
            throw UserAlreadyExistsException::becauseEmailAlreadyExists($newEmail->value());
        }

        // 3. Decide la contraseña:
        //    - Si el campo viene vacío → conserva el hash actual (el usuario no quiso cambiarla)
        //    - Si viene con valor → hashea la nueva contraseña
        $password = ($command->getPassword() !== '')
            ? UserPassword::fromPlainText($command->getPassword())
            : $currentUser->password();

        // 4. Construye el UserModel actualizado
        $userToUpdate = new UserModel(
            $userId,
            new UserName($command->getName()),
            new UserEmail($command->getEmail()),
            $password,
            $command->getRole(),
            $command->getStatus()
        );

        // 5. Persiste y retorna
        return $this->updateUserPort->update($userToUpdate);
    }
}