<?php

declare(strict_types = 1);

namespace App\Domain\Models;

use App\Domain\ValuesObjects\CalificationActividadEvaluada;
use App\Domain\ValuesObjects\CalificationAsignatura;
use App\Domain\ValuesObjects\CalificationCarrera;
use App\Domain\ValuesObjects\CalificationDocente;
use App\Domain\ValuesObjects\CalificationFecha;
use App\Domain\ValuesObjects\CalificationId;
use App\Domain\ValuesObjects\CalificationNota;
use App\Domain\ValuesObjects\CalificationPeriodo;
use App\Domain\ValuesObjects\CalificationPorcentaje;
use App\Domain\ValuesObjects\CalificationUniversidad;
use App\Domain\ValuesObjects\StudentId;

final class CalificationModel
{
    public function __construct(
        private CalificationId              $id,
        private CalificationFecha           $fecha,
        private CalificationDocente         $docente,
        private CalificationAsignatura      $asignatura,
        private CalificationCarrera         $carrera,
        private CalificationUniversidad     $universidad,
        private CalificationPeriodo         $periodo,
        private CalificationActividadEvaluada $actividadEvaluada,
        private CalificationPorcentaje      $porcentaje,
        private StudentId                      $studentId,
        private CalificationNota            $nota,
    ) {}

    public function id(): CalificationId                       { return $this->id; }
    public function fecha(): CalificationFecha                 { return $this->fecha; }
    public function docente(): CalificationDocente             { return $this->docente; }
    public function asignatura(): CalificationAsignatura       { return $this->asignatura; }
    public function carrera(): CalificationCarrera             { return $this->carrera; }
    public function universidad(): CalificationUniversidad     { return $this->universidad; }
    public function periodo(): CalificationPeriodo             { return $this->periodo; }
    public function actividadEvaluada(): CalificationActividadEvaluada { return $this->actividadEvaluada; }
    public function porcentaje(): CalificationPorcentaje       { return $this->porcentaje; }
    public function studentId(): StudentId                        { return $this->studentId; }
    public function nota(): CalificationNota                   { return $this->nota; }

    public function toArray(): array
    {
        return [
            'id'                => $this->id->value(),
            'fecha'             => $this->fecha->toDbFormat(),
            'docente'           => $this->docente->value(),
            'asignatura'        => $this->asignatura->value(),
            'carrera'           => $this->carrera->value(),
            'universidad'       => $this->universidad->value(),
            'periodo'           => $this->periodo->value(),
            'actividadEvaluada' => $this->actividadEvaluada->value(),
            'porcentaje'        => $this->porcentaje->value(),
            'studentId'         => $this->studentId->value(),
            'nota'              => $this->nota->value(),
        ];
    }

}