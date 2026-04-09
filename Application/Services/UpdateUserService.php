<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\UpdateUserUseCase;
use App\Application\Ports\Out\GetUserByEmailPort;
use App\Application\Ports\Out\GetUserByIdPort;
use App\Application\Ports\Out\UpdateUserPort;
use App\Application\Services\Dto\Commands\UpdateUserCommand;
use App\Domain\Exceptions\UserAlreadyExistsException;
use App\Domain\Exceptions\UserNotFoundException;
use App\Domain\Models\UserModel;
use App\Domain\ValuesObjects\UserEmail;
use App\Domain\ValuesObjects\UserId;
use App\Domain\ValuesObjects\UserName;
use App\Domain\ValuesObjects\UserPassword;

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