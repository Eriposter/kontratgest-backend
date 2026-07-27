<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum PaymentModel: string
{
    case SINGLE = 'single';             // Pagamento único
    case INSTALLMENT = 'installment';   // Pagamento parcelar (fixo)
    case MEASUREMENT = 'measurement';   // Por auto de medição (empreitada)
    case CONSIGNMENT = 'consignment';   // À consignação (entrega parcial)
    case MILESTONE = 'milestone';       // Por marcos/milestones

    public function label(): string
    {
        return match ($this) {
            self::SINGLE => 'Pagamento Único',
            self::INSTALLMENT => 'Pagamento Parcelar',
            self::MEASUREMENT => 'Por Auto de Medição',
            self::CONSIGNMENT => 'À Consignação',
            self::MILESTONE => 'Por Marcos (Milestones)',
        };
    }
}