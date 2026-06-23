<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Action extends Model
{
    use Auditable;

    protected $fillable = ['code', 'libelle', 'programme_id', 'actif'];
    protected $casts = ['actif' => 'boolean'];

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function structures(): HasMany
    {
        return $this->hasMany(Structure::class);
    }
}
