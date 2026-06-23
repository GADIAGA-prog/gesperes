<?php

namespace App\Enums;

/** Stratégie de mise en œuvre (Tableau 9 : actions et publics cibles). */
enum StrategieFormation: string
{
    case CAPITALISATION = 'capitalisation';
    case TRANSFERT      = 'transfert';
    case SPECIALISATION = 'specialisation';
    case BENCHMARK      = 'benchmark';
    case IMPLEMENTATION = 'implementation';
    case INNOVATION     = 'innovation';

    public function label(): string
    {
        return match ($this) {
            self::CAPITALISATION => 'Capitalisation',
            self::TRANSFERT      => 'Transfert',
            self::SPECIALISATION => 'Spécialisation / Perfectionnement',
            self::BENCHMARK      => 'Benchmark',
            self::IMPLEMENTATION => 'Implémentation',
            self::INNOVATION     => 'Innovation',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::CAPITALISATION => 'bg-blue-100 text-blue-700',
            self::TRANSFERT      => 'bg-cyan-100 text-cyan-700',
            self::SPECIALISATION => 'bg-indigo-100 text-indigo-700',
            self::BENCHMARK      => 'bg-amber-100 text-amber-800',
            self::IMPLEMENTATION => 'bg-purple-100 text-purple-700',
            self::INNOVATION     => 'bg-green-100 text-green-700',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
