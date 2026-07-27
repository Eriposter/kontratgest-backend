<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum GuaranteeType: string
{
    case BANK_GUARANTEE = 'bank_guarantee';       // Caução bancária
    case INSURANCE = 'insurance';                  // Seguro-caução
    case CASH_DEPOSIT = 'cash_deposit';            // Depósito em numerário
    case PROMISSORY_NOTE = 'promissory_note';      // Nota promissória

    public function label(): string
    {
        return match ($this) {
            self::BANK_GUARANTEE => 'Caução Bancária',
            self::INSURANCE => 'Seguro-Caução',
            self::CASH_DEPOSIT => 'Depósito em Numerário',
            self::PROMISSORY_NOTE => 'Nota Promissória',
        };
    }
}