<?php

namespace App\Enums;

enum Sexe: string
{
    case M = 'M';
    case F = 'F';

    public function label(): string
    {
        return match ($this) {
            self::M => 'Masculin',
            self::F => 'Féminin',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
