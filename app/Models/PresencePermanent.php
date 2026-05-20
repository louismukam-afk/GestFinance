<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresencePermanent extends Model
{
    use HasFactory;

    protected $table = 'presence_permanents';

    protected $fillable = [
        'id_biometrie_import',
        'id_personnel',
        'id_emploi_permanent',
        'id_plage_horaire',
        'id_annee_academique',
        'id_entite',
        'date_presence',
        'jour_semaine',
        'heure_debut_prevue',
        'heure_fin_prevue',
        'heure_debut_reelle',
        'heure_fin_reelle',
        'duree_prevue',
        'duree_realisee',
        'salaire_journalier',
        'montant_du',
        'penalite_montant',
        'statut',
        'observation',
        'id_user',
    ];

    protected $casts = [
        'date_presence' => 'date',
        'duree_prevue' => 'float',
        'duree_realisee' => 'float',
        'salaire_journalier' => 'float',
        'montant_du' => 'float',
        'penalite_montant' => 'float',
    ];

    public function import()
    {
        return $this->belongsTo(BiometrieImport::class, 'id_biometrie_import');
    }

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
    }

    public function emploi()
    {
        return $this->belongsTo(EmploiPermanent::class, 'id_emploi_permanent');
    }

    public function plage()
    {
        return $this->belongsTo(PlageHoraire::class, 'id_plage_horaire');
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
