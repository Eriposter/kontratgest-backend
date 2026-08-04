<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case SCHEDULED = 'scheduled';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';
    case FAILED = 'failed';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pendente',
            self::SCHEDULED => 'Agendado',
            self::APPROVED => 'Aprovado',
            self::PAID => 'Pago',
            self::CANCELLED => 'Cancelado',
            self::REJECTED => 'Rejeitado',
            self::FAILED => 'Falhado',
        };
    }
}