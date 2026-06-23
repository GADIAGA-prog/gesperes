<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRh extends Model
{
    protected $table = 'notifications_rh';

    protected $fillable = ['type', 'cle', 'agent_id', 'titre', 'message', 'niveau', 'lu'];

    protected $casts = ['lu' => 'boolean'];

    public function agent() { return $this->belongsTo(Agent::class); }

    public function scopeNonLues($query) { return $query->where('lu', false); }
}
