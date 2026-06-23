<?php

namespace App\Enums;

/** Axe de formation (Schéma 3 : variables retenues pour la formalisation). */
enum AxeFormation: string
{
    case DOMAINES_SPECIFIQUES = 'domaines_specifiques';
    case POLITIQUE_STRATEGIE  = 'politique_strategie';
    case ADAPTATION_POSTE     = 'adaptation_poste';
    case PROJET_CARRIERE      = 'projet_carriere';

    public function label(): string
    {
        return match ($this) {
            self::DOMAINES_SPECIFIQUES => 'Domaines spécifiques du ministère',
            self::POLITIQUE_STRATEGIE  => 'Politique et stratégie du ministère',
            self::ADAPTATION_POSTE     => 'Adaptation au poste',
            self::PROJET_CARRIERE      => 'Projet personnel de carrière',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
