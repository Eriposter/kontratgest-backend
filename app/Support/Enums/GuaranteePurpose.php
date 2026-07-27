<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum GuaranteePurpose: string
{
    case BID = 'bid';                     // Caução de proposta
    case PERFORMANCE = 'performance';     // Caução de boa execução
    case ADVANCE_PAYMENT = 'advance';     // Caução de adiantamento
    case RETENTION = 'retention';         // Retenção de garantia
    case WARRANTY = 'warranty';           // Garantia pós-obra

    public function label(): string
    {
        return match ($this) {
            self::BID => 'Caução de Proposta',
            self::PERFORMANCE => 'Boa Execução',
            self::ADVANCE_PAYMENT => 'Adiantamento',
            self::RETENTION => 'Retenção de Garantia',
            self::WARRANTY => 'Garantia Pós-Obra',
        };
    }
}