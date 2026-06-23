<?php

namespace App\Enums;

/** Domaine de formation (Schéma 3 : variables retenues pour la formalisation). */
enum DomaineFormation: string
{
    case GRH                   = 'grh';
    case ADMINISTRATION_TRAVAIL = 'administration_travail';
    case TRANSVERSAUX          = 'transversaux';
    case PROJETS_PERSONNELS    = 'projets_personnels';

    public function label(): string
    {
        return match ($this) {
            self::GRH                    => 'Gestion des ressources humaines',
            self::ADMINISTRATION_TRAVAIL => 'Administration du travail',
            self::TRANSVERSAUX           => 'Transversaux',
            self::PROJETS_PERSONNELS     => 'Projets personnels',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
