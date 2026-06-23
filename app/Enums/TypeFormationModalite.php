<?php

namespace App\Enums;

/** Modalité d'organisation d'une action de formation (légende du plan). */
enum TypeFormationModalite: string
{
    case INTRA            = 'intra';
    case EXTRA            = 'extra';
    case DELOCALISE       = 'delocalise';
    case EXTRA_DELOCALISE = 'extra_delocalise';

    public function label(): string
    {
        return match ($this) {
            self::INTRA            => 'Intra (Burkina, formateurs burkinabè)',
            self::EXTRA            => 'Extra (Burkina, formateurs étrangers)',
            self::DELOCALISE       => 'Délocalisé (hors Burkina)',
            self::EXTRA_DELOCALISE => 'Extra délocalisé',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
