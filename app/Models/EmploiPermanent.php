<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmploiPermanent extends Model
{
    use HasFactory;

    protected $table = 'emploi_permanents';

    protected $fillable = [
        'id_personnel',
        'id_plage_horaire',
        'id_annee_academique',
        'id_entite',
        'jour_semaine',
        'date_debut',
        'date_fin',
        'statut',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
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
