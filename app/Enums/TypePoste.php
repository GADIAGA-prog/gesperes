<?php

namespace App\Enums;

/** Type de poste de travail (guide MFPTPS §III.2.2.4). */
enum TypePoste: string
{
    case OPERATIONNEL = 'operationnel';
    case FONCTION = 'fonction';
    case SOUTIEN = 'soutien';

    public function label(): string
    {
        return match ($this) {
            self::OPERATIONNEL => 'Poste opérationnel',
            self::FONCTION => 'Poste fonction',
            self::SOUTIEN => 'Poste soutien',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
