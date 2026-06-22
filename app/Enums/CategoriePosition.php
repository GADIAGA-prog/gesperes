<?php

namespace App\Enums;

/**
 * Grande famille d'une position administrative.
 * (cf. Module 5 : Activité / Sortie temporaire / Sortie définitive)
 */
enum CategoriePosition: string
{
    case ACTIVITE = 'activite';
    case SORTIE_TEMPORAIRE = 'sortie_temporaire';
    case SORTIE_DEFINITIVE = 'sortie_definitive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVITE => 'Activité',
            self::SORTIE_TEMPORAIRE => 'Sortie temporaire',
            self::SORTIE_DEFINITIVE => 'Sortie définitive',
        };
    }

    /** Un agent est-il compté dans l'effectif actif pour cette famille ? */
    public function estActif(): bool
    {
        return $this === self::ACTIVITE;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
