<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgrammeSpecialite extends Model
{
    use HasFactory;

    protected $table = 'programme_specialites';

    protected $fillable = [
        'id_specialite',
        'id_cycle',
        'id_filiere',
        'id_niveau',
        'id_annee_academique',
        'id_entite',
        'id_matiere',
        'code_matiere_specialite',
        'coefficient',
        'coefficient_maximum',
        'type_matiere',
        'semestre',
        'volume_horaire',
        'id_user',
    ];

    public function specialite()
    {
        return $this->belongsTo(specialite::class, 'id_specialite');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'id_matiere');
    }

    public function cycle()
    {
        return $this->belongsTo(cycle::class, 'id_cycle');
    }

    public function filiere()
    {
        return $this->belongsTo(filiere::class, 'id_filiere');
    }

    public function niveau()
    {
        return $this->belongsTo(niveau::class, 'id_niveau');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }

    public function entite()
    {
        return $this->belongsTo(entite::class, 'id_entite');
    }

    public function cours_enseignants()
    {
        return $this->hasMany(CoursEnseignant::class, 'id_programme_specialite');
    }
}
