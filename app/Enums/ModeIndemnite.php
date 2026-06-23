<?php

namespace App\Enums;

/**
 * Mode de calcul d'une indemnité (paramétrable — les taux/montants officiels
 * du décret 2014-427 sont saisis dans le référentiel, jamais codés en dur).
 */
enum ModeIndemnite: string
{
    case MONTANT_FIXE = 'montant_fixe';
    case POURCENTAGE = 'pourcentage';
    case BAREME = 'bareme';

    public function label(): string
    {
        return match ($this) {
            self::MONTANT_FIXE => 'Montant fixe (FCFA / mois)',
            self::POURCENTAGE => 'Pourcentage du salaire indiciaire',
            self::BAREME => 'Barème (décret 2014-427)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
