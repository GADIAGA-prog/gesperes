<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MppOperation extends Model
{
    protected $table = 'mpp_operations';

    protected $fillable = [
        'mpp_procedure_id', 'libelle', 'structure_responsable', 'fait_generateur',
        'taches', 'intervenants', 'resultats', 'delais', 'ordre',
    ];

    public function procedure()
    {
        return $this->belongsTo(MppProcedure::class, 'mpp_procedure_id');
    }
}
