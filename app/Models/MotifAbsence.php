<?php

namespace App\Models;

use App\Enums\CategorieAbsence;
use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class MotifAbsence extends Model
{
    use Auditable;

    protected $table = 'motifs_absence';
    protected $fillable = ['code', 'libelle', 'categorie', 'actif'];
    protected $casts = [
        'actif' => 'boolean',
        'categorie' => CategorieAbsence::class,
    ];
}
