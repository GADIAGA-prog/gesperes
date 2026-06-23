<?php

namespace App\Enums;

/** Public cible d'une action de formation (Schéma 3 / Tableau 9). Multi-sélection. */
enum PublicCibleFormation: string
{
    case TOP_MANAGEMENT = 'top_management';
    case DIRECTEUR      = 'directeur';
    case CHEF_SERVICE   = 'chef_service';
    case CATEGORIE_A    = 'categorie_a';
    case CATEGORIE_B    = 'categorie_b';
    case CATEGORIE_CD   = 'categorie_cd';

    public function label(): string
    {
        return match ($this) {
            self::TOP_MANAGEMENT => 'Top management',
            self::DIRECTEUR      => 'Directeur',
            self::CHEF_SERVICE   => 'Chef de service',
            self::CATEGORIE_A    => 'Catégorie A',
            self::CATEGORIE_B    => 'Catégorie B',
            self::CATEGORIE_CD   => 'Catégorie C / D',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }

    /** Convertit une liste de valeurs en libellés lisibles. */
    public static function labelsFor(?array $valeurs): string
    {
        return collect($valeurs ?? [])
            ->map(fn ($v) => self::tryFrom($v)?->label())
            ->filter()
            ->implode(', ');
    }
}
