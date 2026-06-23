<?php

namespace App\Enums;

/** Solution proposée à une difficulté (fiche de recueil, Annexe 1). */
enum SolutionBesoin: string
{
    case REMUNERATION          = 'remuneration';
    case COACHING              = 'coaching';
    case PARTAGE_INFO          = 'partage_info';
    case MANUEL_PROCEDURES     = 'manuel_procedures';
    case REAFFECTATION         = 'reaffectation';
    case CONDITIONS_MATERIELLES = 'conditions_materielles';
    case FORMATION             = 'formation';
    case AUTRES                = 'autres';

    public function label(): string
    {
        return match ($this) {
            self::REMUNERATION          => 'Rémunération',
            self::COACHING              => 'Coaching',
            self::PARTAGE_INFO          => 'Partage de l\'information',
            self::MANUEL_PROCEDURES     => 'Manuel de procédures',
            self::REAFFECTATION         => 'Réaffectation à un poste convenable',
            self::CONDITIONS_MATERIELLES => 'Amélioration des conditions matérielles',
            self::FORMATION             => 'Formation',
            self::AUTRES                => 'Autres',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
