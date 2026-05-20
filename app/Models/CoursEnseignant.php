<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoursEnseignant extends Model
{
    use HasFactory;

    protected $table = 'cours_enseignants';

    protected $fillable = [
        'id_personnel',
        'id_programme_specialite',
        'id_taux_horaire',
        'id_salle',
        'id_cycle',
        'id_filiere',
        'id_niveau',
        'id_specialite',
        'id_annee_academique',
        'id_entite',
        'date_debut',
        'date_fin',
        'statut',
        'semestre',
        'volume_horaire_prevu',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'semestre' => 'integer',
        'volume_horaire_prevu' => 'float',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function programme()
    {
        return $this->belongsTo(ProgrammeSpecialite::class, 'id_programme_specialite');
    }

    public function taux_horaire()
    {
        return $this->belongsTo(TauxHoraire::class, 'id_taux_horaire');
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class, 'id_salle');
    }

    public function seances()
    {
        return $this->hasMany(SeanceCours::class, 'id_cours_enseignant');
    }
}
