<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MppProcessus extends Model
{
    protected $table = 'mpp_processus';

    protected $fillable = ['numero', 'code', 'libelle', 'ordre'];

    public function procedures()
    {
        return $this->hasMany(MppProcedure::class)->orderBy('ordre')->orderBy('id');
    }
}
