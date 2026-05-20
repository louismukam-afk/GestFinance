<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinePersonnel extends Model
{
    use HasFactory;

    protected $table = 'discipline_personnels';

    protected $fillable = [
        'id_personnel',
        'id_cours_enseignant',
        'id_seance_cours',
        'id_biometrie_import',
        'id_annee_academique',
        'id_entite',
        'type_discipline',
        'date_discipline',
        'duree_heures',
        'minutes_retard',
        'motif',
        'statut',
        'date_justification',
        'motif_justification',
        'preuves',
        'id_user',
    ];

    protected $casts = [
        'date_discipline' => 'date',
        'date_justification' => 'date',
        'duree_heures' => 'float',
        'preuves' => 'array',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function cours()
    {
        return $this->belongsTo(CoursEnseignant::class, 'id_cours_enseignant');
    }

    public function seance()
    {
        return $this->belongsTo(SeanceCours::class, 'id_seance_cours');
    }

    public function import()
    {
        return $this->belongsTo(BiometrieImport::class, 'id_biometrie_import');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }

    public function entite()
    {
        return $this->belongsTo(entite::class, 'id_entite');
    }
}
