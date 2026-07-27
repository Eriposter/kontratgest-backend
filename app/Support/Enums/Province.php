<?php

declare(strict_types=1);

namespace App\Support\Enums;

enum Province: string
{
    case BENGUELA = 'benguela';
    case BENGUO = 'benguo';
    case BIE = 'bie';
    case CABINDA = 'cabinda';
    case CUANDO = 'cuando';
    case CUBANGO = 'cubango';
    case CUANZA_NORTE = 'cuanza_norte';
    case CUANZA_SUL = 'cuanza_sul';
    case CUNENE = 'cunene';
    case HUAMBO = 'huambo';
    case HUILA = 'huila';
    case ICOLO_E_BENGO = 'icolo_e_bengo';
    case LUANDA = 'luanda';
    case LUNDA_NORTE = 'lunda_norte';
    case LUNDA_SUL = 'lunda_sul';
    case MALANJE = 'malanje';
    case MOXICO = 'moxico';
    case MOXICO_LESTE = 'moxico_leste';
    case NAMIBE = 'namibe';
    case UIGE = 'uige';
    case ZAIRE = 'zaire';

    public function label(): string
    {
        return match ($this) {
            self::BENGUELA => 'Benguela',
            self::BENGUO => 'Bengo',
            self::BIE => 'Bié',
            self::CABINDA => 'Cabinda',
            self::CUANDO => 'Cuando',
            self::CUBANGO => 'Cubango',
            self::CUANZA_NORTE => 'Cuanza Norte',
            self::CUANZA_SUL => 'Cuanza Sul',
            self::CUNENE => 'Cunene',
            self::HUAMBO => 'Huambo',
            self::HUILA => 'Huíla',
            self::ICOLO_E_BENGO => 'Icolo e Bengo',
            self::LUANDA => 'Luanda',
            self::LUNDA_NORTE => 'Lunda Norte',
            self::LUNDA_SUL => 'Lunda Sul',
            self::MALANJE => 'Malanje',
            self::MOXICO => 'Moxico',
            self::MOXICO_LESTE => 'Moxico Leste',
            self::NAMIBE => 'Namibe',
            self::UIGE => 'Uíge',
            self::ZAIRE => 'Zaire',
        };
    }
}