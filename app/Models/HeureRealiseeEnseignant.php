<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeureRealiseeEnseignant extends Model
{
    use HasFactory;

    protected $table = 'heures_realisees_enseignants';

    protected $fillable = [
        'id_biometrie_import',
        'id_cours_enseignant',
        'id_seance_cours',
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
        'date_seance',
        'jour_semaine',
        'id_plage_horaire',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_debut_reelle',
        'heure_fin_reelle',
        'duree_prevue',
        'duree_realisee',
        'montant_taux',
        'montant_total',
        'statut',
        'observation',
        'id_user',
    ];

    protected $casts = [
        'date_seance' => 'date',
        'duree_prevue' => 'float',
        'duree_realisee' => 'float',
        'montant_taux' => 'float',
        'montant_total' => 'float',
    ];

    public function import()
    {
        return $this->belongsTo(BiometrieImport::class, 'id_biometrie_import');
    }

    public function cours()
    {
        return $this->belongsTo(CoursEnseignant::class, 'id_cours_enseignant');
    }

    public function seance()
    {
        return $this->belongsTo(SeanceCours::class, 'id_seance_cours');
    }

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }
}
