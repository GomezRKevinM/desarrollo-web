<?php

declare (strict_types = 1);

namespace App\Infrastructure\Entrypoints\Web\Controllers;

use App\Application\Ports\In\CreateCalificationUseCase;
use App\Application\Ports\In\DeleteCalificationUseCase;
use App\Application\Ports\In\GetAllCalificationsUseCase;
use App\Application\Ports\In\GetByCalificationIdUseCase;
use App\Application\Ports\In\UpdateCalificationUseCase;
use App\Application\Services\Dto\Queries\GetAllCalificationsQuery;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CalificationResponse;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\CreateCalificationWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Dto\UpdateCalificationWebRequest;
use App\Infrastructure\Entrypoints\Web\Controllers\Mapper\CalificationWebMapper;

final class CalificationController
{
        public function __construct(
            private CreateCalificationUseCase $createCalificationUseCase,
            private UpdateCalificationUseCase $updateCalificationUseCase,
            private DeleteCalificationUseCase $deleteCalificationUseCase,
            private GetByCalificationIdUseCase $getByCalificationIdUseCase,
            private GetAllCalificationsUseCase $getAllCalificationsUseCase,
            private CalificationWebMapper $mapper
        ) {}

        /** @return CalificationResponse[] */
        public function index(): array
        {
            $califications = $this->getAllCalificationsUseCase->execute(new GetAllCalificationsQuery());
            return $this->mapper->fromModelsToResponses($califications);
        }

        public function show(string $id): CalificationResponse
        {
           $query = $this->mapper->fromIdToGetByIdQuery($id);
           $calification = $this->getByCalificationIdUseCase->execute($query);
           return $this->mapper->fromModelToResponse($calification);
        }

        public function store(CreateCalificationWebRequest $request): CalificationResponse
        {
            $command = $this->mapper->fromCreateRequestToCommand($request);
            $calification = $this->createCalificationUseCase->execute($command);
            return $this->mapper->fromModelToResponse($calification);
        }

        public function update(UpdateCalificationWebRequest $request): CalificationResponse
        {
            $command = $this->mapper->fromUpdateRequestToCommand($request);
            $calification = $this->updateCalificationUseCase->execute($command);
            return $this->mapper->fromModelToResponse($calification);
        }

        public function delete(string $id): void
        {
            $command = $this->mapper->fromIdToDeleteCommand($id);
            $this->deleteUserUseCase->execute($command);
        }

}