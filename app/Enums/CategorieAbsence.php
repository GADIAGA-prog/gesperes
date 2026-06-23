<?php

namespace App\Enums;

/**
 * Nature d'une absence / d'un motif d'absence, qui détermine son imputation
 * sur les droits de l'agent (cf. règles de gestion congés/absences).
 *
 *  - AUTORISATION : autorisation d'absence — imputée sur le quota de 10 jours ;
 *    au-delà de 10 jours, le dépassement est déduit du congé annuel (30 jours).
 *  - CONGE        : congé annuel — imputé directement sur le quota de 30 jours.
 *  - JUSTIFIEE    : absence couverte par une pièce justificative (maladie, mission,
 *    formation, maternité…) — non imputée sur les quotas.
 *  - INJUSTIFIEE  : absence sans motif — non imputée mais relevée pour mesures
 *    disciplinaires.
 */
enum CategorieAbsence: string
{
    case AUTORISATION = 'autorisation';
    case CONGE = 'conge';
    case JUSTIFIEE = 'justifiee';
    case INJUSTIFIEE = 'injustifiee';

    public function label(): string
    {
        return match ($this) {
            self::AUTORISATION => "Autorisation d'absence",
            self::CONGE => 'Congé annuel',
            self::JUSTIFIEE => 'Absence justifiée',
            self::INJUSTIFIEE => 'Absence injustifiée',
        };
    }

    /** Imputée sur le quota d'autorisations d'absence (10 jours). */
    public function imputeAutorisation(): bool
    {
        return $this === self::AUTORISATION;
    }

    /** Imputée (directement) sur le quota de congé annuel (30 jours). */
    public function imputeConge(): bool
    {
        return $this === self::CONGE;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
