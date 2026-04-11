<?php

declare(strict_types=1);

namespace App\Application\Services\Dto\Commands;

final class CreateCalificationCommand
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
        float $nota
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
    }

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

