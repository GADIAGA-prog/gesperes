<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Localite extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'zone_id', 'region', 'province', 'commune', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function zone() { return $this->belongsTo(Zone::class); }
}
