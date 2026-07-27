<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum Currency: string
{
    case AOA = 'AOA';
    case USD = 'USD';
    case EUR = 'EUR';

    public function symbol(): string
    {
        return match ($this) {
            self::AOA => 'Kz',
            self::USD => '$',
            self::EUR => '€',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::AOA => 'Kwanza Angolano',
            self::USD => 'Dólar Americano',
            self::EUR => 'Euro',
        };
    }

    public function decimals(): int
    {
        return match ($this) {
            self::AOA => 2,
            self::USD => 2,
            self::EUR => 2,
        };
    }
}