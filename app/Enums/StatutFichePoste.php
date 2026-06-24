<?php

namespace App\Enums;

/**
 * Cycle de vie d'une fiche de poste (guide MFPTPS §IV) :
 * brouillon → validée par le supérieur immédiat → adoptée (DRH/comité).
 * La révision d'une fiche adoptée la repasse en brouillon (nouvelle version).
 */
enum StatutFichePoste: string
{
    case BROUILLON = 'brouillon';
    case VALIDEE_SUPERIEUR = 'validee_superieur';
    case ADOPTEE = 'adoptee';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::VALIDEE_SUPERIEUR => 'Validée (supérieur)',
            self::ADOPTEE => 'Adoptée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BROUILLON => 'bg-gray-100 text-gray-700',
            self::VALIDEE_SUPERIEUR => 'bg-amber-100 text-amber-700',
            self::ADOPTEE => 'bg-green-100 text-green-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
