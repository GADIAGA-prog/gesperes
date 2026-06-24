<?php

namespace App\Enums;

/**
 * Cycle de vie d'une fiche de poste.
 * Lot 1 : brouillon / adoptée. Le workflow complet (validations) viendra au Lot 2.
 */
enum StatutFichePoste: string
{
    case BROUILLON = 'brouillon';
    case ADOPTEE = 'adoptee';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::ADOPTEE => 'Adoptée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BROUILLON => 'bg-gray-100 text-gray-700',
            self::ADOPTEE => 'bg-green-100 text-green-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
