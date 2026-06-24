<?php

namespace App\Enums;

/**
 * Position hiérarchique du poste — fournit le chiffre de codification
 * (guide MFPTPS §III.3.1.1 : DG=1, Directeur=2, Chef de service=3, Agent=4).
 */
enum PositionHierarchique: string
{
    case DG = 'dg';
    case DIRECTEUR = 'directeur';
    case CHEF_SERVICE = 'chef_service';
    case AGENT = 'agent';

    public function label(): string
    {
        return match ($this) {
            self::DG => 'Directeur général et assimilé',
            self::DIRECTEUR => 'Directeur de service et assimilé',
            self::CHEF_SERVICE => 'Chef de service et assimilé',
            self::AGENT => 'Agent',
        };
    }

    /** Chiffre utilisé dans la codification du poste. */
    public function chiffre(): int
    {
        return match ($this) {
            self::DG => 1,
            self::DIRECTEUR => 2,
            self::CHEF_SERVICE => 3,
            self::AGENT => 4,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
