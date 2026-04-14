<?php

declare(strict_types=1);

namespace App\Infrastructure\Entrypoints\Web\Controllers\Dto;

final readonly class UpdateCalificationWebRequest
{
    public function __construct(
        private string $id,
        private string $fecha,
        private string $docente,
        private string $asignatura,
        private string $carrera,
        private string $universidad,
        private string $periodo,
        private string $actividadEvaluada,
        private float $porcentaje,
        private string $studentId,
        private float $nota,
    ) {}

    public function getId(): string { return $this->id; }
    public function getFecha(): string { return $this->fecha; }
    public function getDocente(): string { return $this->docente; }
    public function getAsignatura(): string { return $this->asignatura; }
    public function getCarrera(): string { return $this->carrera; }
    public function getUniversidad(): string { return $this->universidad; }
    public function getPeriodo(): string { return $this->periodo; }
    public function getActividadEvaluada(): string { return $this->actividadEvaluada; }
    public function getPorcentaje(): float { return $this->porcentaje; }
    public function getStudentId(): string { return $this->studentId; }
    public function getNota(): float { return $this->nota; }
}