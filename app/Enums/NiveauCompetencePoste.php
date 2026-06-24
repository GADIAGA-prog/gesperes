<?php

namespace App\Enums;

/**
 * Niveau de compétence requis pour une fiche de poste (guide MFPTPS §III.3.2.2 :
 * Application / Analyse / Expertise). Distinct de NiveauCompetence (formation).
 */
enum NiveauCompetencePoste: string
{
    case APPLICATION = 'application';
    case ANALYSE = 'analyse';
    case EXPERTISE = 'expertise';

    public function label(): string
    {
        return match ($this) {
            self::APPLICATION => 'Application',
            self::ANALYSE => 'Analyse',
            self::EXPERTISE => 'Expertise',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
