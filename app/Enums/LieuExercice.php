<?php

namespace App\Enums;

/**
 * Indique si l'agent exerce "En classe" (enseignant) ou "Au bureau"
 * (administratif). Déduit de l'emploi (champ enseignant).
 */
enum LieuExercice: string
{
    case EN_CLASSE = 'en_classe';
    case AU_BUREAU = 'au_bureau';

    public function label(): string
    {
        return match ($this) {
            self::EN_CLASSE => 'En classe',
            self::AU_BUREAU => 'Au bureau',
        };
    }
}
