<?php

namespace App\Support\Enums;

enum ProcedureStatus: string
{
    case PLANNED = 'planned';
    case IN_PROGRESS = 'in_progress';
    case EVALUATION = 'evaluation';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::PLANNED => 'Planeado',
            self::IN_PROGRESS => 'Em Curso',
            self::EVALUATION => 'Em Avaliação',
            self::COMPLETED => 'Finalizado',
            self::CANCELLED => 'Cancelado',
            self::FAILED => 'Deserto',
        };
    }
}