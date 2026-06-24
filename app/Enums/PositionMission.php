<?php

namespace App\Enums;

/** Position du poste par rapport à la mission (guide MFPTPS §III.3.1.1). */
enum PositionMission: string
{
    case PILOTAGE = 'pilotage';
    case REALISATION = 'realisation';
    case SUPPORT = 'support';

    public function label(): string
    {
        return match ($this) {
            self::PILOTAGE => 'Pilotage',
            self::REALISATION => 'Réalisation',
            self::SUPPORT => 'Support',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
