<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlageHoraire extends Model
{
    use HasFactory;

    protected $table = 'plage_horaires';

    protected $fillable = [
        'libelle',
        'heure_debut',
        'heure_fin',
        'duree_payable',
        'type_plage',
        'type_personnel',
        'periode_journee',
        'format_plage',
        'ordre',
        'statut',
        'id_user',
    ];
}
