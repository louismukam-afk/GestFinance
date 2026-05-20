<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcompteSalaire extends Model
{
    use HasFactory;

    protected $table = 'acomptes_salaire';

    protected $fillable = [
        'id_personnel',
        'date_acompte',
        'montant',
        'periode_imputation',
        'motif',
        'statut',
        'id_bulletin_paie',
        'id_user',
    ];

    protected $casts = [
        'date_acompte' => 'date',
        'montant' => 'float',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }
}
