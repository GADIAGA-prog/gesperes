<?php

namespace App\Enums;

enum StatutFormation: string
{
    case PLANIFIEE = 'planifiee';
    case EN_COURS = 'en_cours';
    case TERMINEE = 'terminee';
    case ANNULEE = 'annulee';

    public function label(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'Planifiée',
            self::EN_COURS => 'En cours',
            self::TERMINEE => 'Terminée',
            self::ANNULEE => 'Annulée',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PLANIFIEE => 'bg-blue-100 text-blue-700',
            self::EN_COURS => 'bg-amber-100 text-amber-800',
            self::TERMINEE => 'bg-green-100 text-green-700',
            self::ANNULEE => 'bg-gray-200 text-gray-600',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
