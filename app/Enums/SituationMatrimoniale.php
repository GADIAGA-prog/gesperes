<?php

namespace App\Enums;

enum SituationMatrimoniale: string
{
    case CELIBATAIRE = 'celibataire';
    case MARIE = 'marie';
    case DIVORCE = 'divorce';
    case VEUF = 'veuf';

    public function label(): string
    {
        return match ($this) {
            self::CELIBATAIRE => 'Célibataire',
            self::MARIE => 'Marié(e)',
            self::DIVORCE => 'Divorcé(e)',
            self::VEUF => 'Veuf/Veuve',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
