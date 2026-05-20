<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TauxHoraire extends Model
{
    use HasFactory;

    protected $table = 'taux_horaires';

    protected $fillable = [
        'libelle',
        'montant',
        'par_defaut',
        'statut',
        'id_user',
    ];

    protected $casts = [
        'par_defaut' => 'boolean',
        'montant' => 'float',
    ];
}
