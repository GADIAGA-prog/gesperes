<?php

namespace App\Enums;

enum TypeEvenementCarriere: string
{
    case AVANCEMENT_ECHELON = 'avancement_echelon';
    case AVANCEMENT_CLASSE = 'avancement_classe';
    case PROMOTION = 'promotion';
    case NOMINATION = 'nomination';
    case CHANGEMENT_POSITION = 'changement_position';

    public function label(): string
    {
        return match ($this) {
            self::AVANCEMENT_ECHELON => "Avancement d'échelon",
            self::AVANCEMENT_CLASSE => 'Changement de classe',
            self::PROMOTION => 'Promotion (catégorie / échelle)',
            self::NOMINATION => 'Nomination (fonction / poste)',
            self::CHANGEMENT_POSITION => 'Changement de position administrative',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AVANCEMENT_ECHELON => 'bg-blue-100 text-blue-700',
            self::AVANCEMENT_CLASSE => 'bg-indigo-100 text-indigo-700',
            self::PROMOTION => 'bg-green-100 text-green-700',
            self::NOMINATION => 'bg-amber-100 text-amber-700',
            self::CHANGEMENT_POSITION => 'bg-purple-100 text-purple-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
