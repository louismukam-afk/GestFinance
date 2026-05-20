<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeanceCours extends Model
{
    use HasFactory;

    protected $table = 'seance_cours';

    protected $fillable = [
        'id_cours_enseignant',
        'id_plage_horaire',
        'jour_semaine',
        'date_seance',
        'duree_heures',
        'statut',
        'id_user',
    ];

    protected $casts = [
        'date_seance' => 'date',
        'duree_heures' => 'float',
    ];

    public function cours()
    {
        return $this->belongsTo(CoursEnseignant::class, 'id_cours_enseignant');
    }

    public function plage()
    {
        return $this->belongsTo(PlageHoraire::class, 'id_plage_horaire');
    }
}
