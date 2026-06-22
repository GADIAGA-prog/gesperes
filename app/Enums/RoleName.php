<?php

namespace App\Enums;

enum RoleName: string
{
    case SUPER_ADMIN = 'super-admin';
    case ADMIN_NATIONAL = 'admin-national';
    case ADMIN_REGIONAL = 'admin-regional';
    case RESPONSABLE_STRUCTURE = 'responsable-structure';
    case AGENT_RH = 'agent-rh';
    case CONSULTATION = 'consultation';
    case AGENT_INDIVIDUEL = 'agent-individuel';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN_NATIONAL => 'Administrateur national',
            self::ADMIN_REGIONAL => 'Administrateur régional',
            self::RESPONSABLE_STRUCTURE => 'Responsable structure',
            self::AGENT_RH => 'Agent RH',
            self::CONSULTATION => 'Consultation',
            self::AGENT_INDIVIDUEL => 'Agent individuel',
        };
    }

    public static function values(): array
    {
        return array_map(fn ($c) => $c->value, self::cases());
    }
}
