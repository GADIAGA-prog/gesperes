<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'region_id', 'chef_lieu', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function localites(): HasMany
    {
        return $this->hasMany(Localite::class);
    }
}
