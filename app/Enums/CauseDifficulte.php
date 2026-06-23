<?php

namespace App\Enums;

/** Cause d'une difficulté d'exécution des tâches (fiche de recueil, Annexe 1). */
enum CauseDifficulte: string
{
    case MOTIVATION           = 'motivation';
    case APPUI                = 'appui';
    case COMMUNICATION        = 'communication';
    case OUTILS               = 'outils';
    case TACHES_INHABITUELLES = 'taches_inhabituelles';
    case FEEDBACK             = 'feedback';
    case COMPETENCE           = 'competence';
    case AUTRES               = 'autres';

    public function label(): string
    {
        return match ($this) {
            self::MOTIVATION           => 'Manque de motivation',
            self::APPUI                => 'Pas d\'appui du responsable / des collègues',
            self::COMMUNICATION        => 'Manque de communication',
            self::OUTILS               => 'Manque d\'outils ou de ressources',
            self::TACHES_INHABITUELLES => 'Tâches inhabituelles',
            self::FEEDBACK             => 'Pas de feedback',
            self::COMPETENCE           => 'Manque de compétence',
            self::AUTRES               => 'Autres',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
