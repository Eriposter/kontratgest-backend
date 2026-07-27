<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum EntityType: string
{
    case SUPPLIER = 'supplier';
    case CLIENT = 'client';
    case CONTRACTOR = 'contractor';
    case SUBCONTRACTOR = 'subcontractor';
    case PUBLIC_ENTITY = 'public_entity';
    case CONSULTANT = 'consultant';

    public function label(): string
    {
        return match ($this) {
            self::SUPPLIER => 'Fornecedor',
            self::CLIENT => 'Cliente',
            self::CONTRACTOR => 'Empreiteiro',
            self::SUBCONTRACTOR => 'Subempreiteiro',
            self::PUBLIC_ENTITY => 'Entidade Pública',
            self::CONSULTANT => 'Consultor',
        };
    }
}