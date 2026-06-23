<?php

namespace App\Models;

use App\Support\Auditable;
use Illuminate\Database\Eloquent\Model;

class EnveloppeLigne extends Model
{
    use Auditable;

    protected $table = 'enveloppe_lignes';

    protected $fillable = ['enveloppe_personnel_id', 'libelle', 'montant_n1', 'montant_n2', 'montant_n3', 'ordre'];

    protected $casts = [
        'montant_n1' => 'decimal:2',
        'montant_n2' => 'decimal:2',
        'montant_n3' => 'decimal:2',
        'ordre' => 'integer',
    ];

    public function enveloppe()
    {
        return $this->belongsTo(EnveloppePersonnel::class, 'enveloppe_personnel_id');
    }
}
