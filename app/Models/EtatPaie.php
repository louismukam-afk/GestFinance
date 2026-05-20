<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtatPaie extends Model
{
    use HasFactory;

    protected $table = 'etats_paie';

    protected $fillable = [
        'reference',
        'periode_debut',
        'periode_fin',
        'id_annee_academique',
        'id_entite',
        'date_generation',
        'nombre_employes',
        'total_gains',
        'total_retenues',
        'total_penalites',
        'total_sanctions',
        'total_acomptes',
        'total_net_a_payer',
        'statut',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'periode_debut' => 'date',
        'periode_fin' => 'date',
        'date_generation' => 'datetime',
        'total_gains' => 'float',
        'total_retenues' => 'float',
        'total_penalites' => 'float',
        'total_sanctions' => 'float',
        'total_acomptes' => 'float',
        'total_net_a_payer' => 'float',
    ];

    public function lignes()
    {
        return $this->hasMany(LigneEtatPaie::class, 'id_etat_paie');
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
