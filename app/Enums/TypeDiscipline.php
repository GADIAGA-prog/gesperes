<?php

namespace App\Enums;

enum TypeDiscipline: string
{
    case DEMANDE_EXPLICATION = 'demande_explication';
    case SANCTION = 'sanction';
    case RECOURS = 'recours';

    public function label(): string
    {
        return match ($this) {
            self::DEMANDE_EXPLICATION => "Demande d'explication",
            self::SANCTION => 'Sanction',
            self::RECOURS => 'Recours',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DEMANDE_EXPLICATION => 'bg-amber-100 text-amber-800',
            self::SANCTION => 'bg-red-100 text-red-700',
            self::RECOURS => 'bg-blue-100 text-blue-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
