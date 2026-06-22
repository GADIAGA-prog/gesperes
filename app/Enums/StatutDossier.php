<?php

namespace App\Enums;

enum StatutDossier: string
{
    case BROUILLON = 'brouillon';
    case INCOMPLET = 'incomplet';
    case COMPLET = 'complet';
    case VALIDE = 'valide';

    public function label(): string
    {
        return match ($this) {
            self::BROUILLON => 'Brouillon',
            self::INCOMPLET => 'Incomplet',
            self::COMPLET => 'Complet',
            self::VALIDE => 'Validé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BROUILLON => 'bg-gray-100 text-gray-700',
            self::INCOMPLET => 'bg-amber-100 text-amber-800',
            self::COMPLET => 'bg-blue-100 text-blue-800',
            self::VALIDE => 'bg-green-100 text-green-800',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
