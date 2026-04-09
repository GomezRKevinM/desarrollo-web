<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Ports\In\GetAllStudentsUseCase;
use App\Application\Ports\Out\GetAllStudentsPort;
use App\Application\Services\Dto\Queries\GetAllStudentsQuery;
use App\Domain\Models\StudentModel;

final class GetAllStudentsService implements GetAllStudentsUseCase
{
    private GetAllStudentsPort $getAllStudentsPort;

    public function __construct(GetAllStudentsPort $getAllStudentsPort)
    {
        $this->getAllStudentsPort = $getAllStudentsPort;
    }

    /** @return StudentModel[] */
    public function execute(GetAllStudentsQuery $query): array
    {
        return $this->getAllStudentsPort->getAll();
    }
}
