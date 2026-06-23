<?php

namespace App\Enums;

/**
 * Étape (position dans le circuit administratif) d'un dossier suivi.
 * Ordonnée du dépôt jusqu'à la clôture.
 */
enum EtapeDossier: string
{
    case RECEPTION    = 'reception';
    case INSTRUCTION  = 'instruction';
    case VERIFICATION = 'verification';
    case VALIDATION   = 'validation';
    case SIGNATURE    = 'signature';
    case NOTIFICATION = 'notification';
    case CLOS         = 'clos';

    public function label(): string
    {
        return match ($this) {
            self::RECEPTION    => 'Réception',
            self::INSTRUCTION  => 'Instruction / étude',
            self::VERIFICATION => 'Vérification',
            self::VALIDATION   => 'Validation',
            self::SIGNATURE    => 'Signature',
            self::NOTIFICATION => 'Notification / transmission',
            self::CLOS         => 'Clôturé / archivé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RECEPTION    => 'bg-gray-100 text-gray-700',
            self::INSTRUCTION  => 'bg-blue-100 text-blue-700',
            self::VERIFICATION => 'bg-indigo-100 text-indigo-700',
            self::VALIDATION   => 'bg-amber-100 text-amber-800',
            self::SIGNATURE    => 'bg-purple-100 text-purple-700',
            self::NOTIFICATION => 'bg-cyan-100 text-cyan-700',
            self::CLOS         => 'bg-green-100 text-green-700',
        };
    }

    /** L'étape correspond-elle à un dossier sorti du circuit (clôturé) ? */
    public function estTerminale(): bool
    {
        return $this === self::CLOS;
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->all();
    }
}
