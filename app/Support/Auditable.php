<?php

namespace App\Support;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Trait standard d'audit : journalise création / modification / suppression
 * en ne gardant que les attributs réellement modifiés.
 */
trait Auditable
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logFillable()
            ->useLogName(strtolower(class_basename($this)));
    }
}
