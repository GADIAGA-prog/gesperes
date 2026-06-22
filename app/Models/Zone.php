<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function localites() { return $this->hasMany(Localite::class); }
}
