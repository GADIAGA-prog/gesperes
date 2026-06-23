<?php

namespace App\Enums;

/** Cycle de vie d'un plan ou d'un programme annuel de formation. */
enum StatutPlanFormation: string
{
    case BROUILLON = 'brouillon';
    case VALIDE    = 'valide';
    case CLOS      = 'clos';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::VALIDE    => 'Validé',
            self::CLOS      => 'Clôturé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BROUILLON => 'bg-amber-100 text-amber-800',
            self::VALIDE    => 'bg-green-100 text-green-700',
            self::CLOS      => 'bg-gray-200 text-gray-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
