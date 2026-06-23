<?php

namespace App\Enums;

/** Fréquence d'exécution d'une tâche (fiche de recueil, Annexe 1). */
enum FrequenceTache: string
{
    case HEBDOMADAIRE = 'hebdomadaire';
    case MENSUELLE    = 'mensuelle';
    case SEMESTRIELLE = 'semestrielle';

    public function label(): string
    {
        return match ($this) {
            self::HEBDOMADAIRE => 'Chaque jour ou semaine',
            self::MENSUELLE    => 'Chaque mois ou trimestre',
            self::SEMESTRIELLE => 'Chaque semestre ou annuelle',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
