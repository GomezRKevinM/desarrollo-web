<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapters\Persistence\MySQL\Entity;

final class CalificationEntity
{
    private string $id;
    private string $fecha;
    private string $docente;
    private string $asignatura;
    private string $carrera;
    private string $universidad;
    private string $periodo;
    private string $actividadEvaluada;
    private float $porcentaje;
    private string $studentId;
    private float $nota;
    private ?string $createdAt;
    private ?string $updatedAt;

    public function __construct(
        string $id,
        string $fecha,
        string $docente,
        string $asignatura,
        string $carrera,
        string $universidad,
        string $periodo,
        string $actividadEvaluada,
        float $porcentaje,
        string $studentId,
        float $nota,
        ?string $createdAt = null,
        ?string $updatedAt = null
    )
    {
        $this->id = trim($id);
        $this->fecha = trim($fecha);
        $this->docente = trim($docente);
        $this->asignatura = trim($asignatura);
        $this->carrera = trim($carrera);
        $this->universidad = trim($universidad);
        $this->periodo = trim($periodo);
        $this->actividadEvaluada = trim($actividadEvaluada);
        $this->porcentaje = $porcentaje;
        $this->studentId = trim($studentId);
        $this->nota = $nota;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function id(): string { return $this->id; }
    public function fecha(): string { return $this->fecha; }
    public function docente(): string { return $this->docente; }
    public function asignatura(): string { return $this->asignatura; }
    public function carrera(): string { return $this->carrera; }
    public function universidad(): string { return $this->universidad; }
    public function periodo(): string { return $this->periodo; }
    public function actividadEvaluada(): string { return $this->actividadEvaluada; }
    public function porcentaje(): float { return $this->porcentaje; }
    public function studentId(): string { return $this->studentId; }
    public function nota(): float { return $this->nota; }
    public function createdAt(): ?string { return $this->createdAt; }
    public function updatedAt(): ?string { return $this->updatedAt; }
}

