<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MppProcedure extends Model
{
    protected $table = 'mpp_procedures';

    protected $fillable = ['mpp_processus_id', 'libelle', 'ordre'];

    public function processus()
    {
        return $this->belongsTo(MppProcessus::class, 'mpp_processus_id');
    }

    public function operations()
    {
        return $this->hasMany(MppOperation::class)->orderBy('ordre')->orderBy('id');
    }
}
