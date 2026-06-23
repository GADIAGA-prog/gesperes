<?php

namespace App\Enums;

/**
 * Cycle de vie d'une demande de congé / d'autorisation d'absence.
 * Seules les demandes VALIDÉES sont imputées sur les soldes de l'agent.
 */
enum StatutConge: string
{
    case DEMANDE = 'demande';
    case VALIDE = 'valide';
    case REFUSE = 'refuse';
    case ANNULE = 'annule';

    public function label(): string
    {
        return match ($this) {
            self::DEMANDE => 'En attente',
            self::VALIDE => 'Validé',
            self::REFUSE => 'Refusé',
            self::ANNULE => 'Annulé',
        };
    }

    public function badge(): string
    {
        return match ($this) {
            self::DEMANDE => 'bg-amber-100 text-amber-800',
            self::VALIDE => 'bg-green-100 text-green-800',
            self::REFUSE => 'bg-red-100 text-red-800',
            self::ANNULE => 'bg-gray-100 text-gray-600',
        };
    }

    /** Imputé sur les soldes uniquement si validé. */
    public function compteDansSolde(): bool
    {
        return $this === self::VALIDE;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
