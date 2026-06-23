<?php

namespace App\Enums;

/** Niveau de compétence visé par une action (Schéma 3). */
enum NiveauCompetence: string
{
    case ANALYSE   = 'analyse';
    case TRANSFERT = 'transfert';
    case INNOVATION = 'innovation';

    public function label(): string
    {
        return match ($this) {
            self::ANALYSE    => 'Analyse',
            self::TRANSFERT  => 'Transfert',
            self::INNOVATION => 'Innovation',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
