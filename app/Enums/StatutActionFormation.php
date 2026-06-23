<?php

namespace App\Enums;

/** Cycle de vie d'une action de formation planifiée. */
enum StatutActionFormation: string
{
    case PLANIFIEE = 'planifiee';
    case EN_COURS  = 'en_cours';
    case REALISEE  = 'realisee';
    case ANNULEE   = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'Planifiée',
            self::EN_COURS  => 'En cours',
            self::REALISEE  => 'Réalisée',
            self::ANNULEE   => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'bg-blue-100 text-blue-700',
            self::EN_COURS  => 'bg-amber-100 text-amber-800',
            self::REALISEE  => 'bg-green-100 text-green-700',
            self::ANNULEE   => 'bg-red-100 text-red-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
