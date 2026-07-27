<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum ContractType: string
{
    case SERVICE = 'service';
    case WORKS = 'works';           // Empreitada
    case SUPPLY = 'supply';         // Fornecimento
    case LEASE = 'lease';           // Arrendamento
    case CONSULTANCY = 'consultancy';
    case PARTNERSHIP = 'partnership';

    public function label(): string
    {
        return match ($this) {
            self::SERVICE => 'Prestação de Serviços',
            self::WORKS => 'Empreitada',
            self::SUPPLY => 'Fornecimento',
            self::LEASE => 'Arrendamento',
            self::CONSULTANCY => 'Consultoria',
            self::PARTNERSHIP => 'Parceria',
        };
    }
}