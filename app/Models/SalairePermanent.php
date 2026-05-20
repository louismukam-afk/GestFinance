<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalairePermanent extends Model
{
    use HasFactory;

    protected $table = 'salaires_permanents';

    protected $fillable = [
        'id_personnel',
        'id_annee_academique',
        'id_entite',
        'montant_mensuel',
        'date_debut',
        'date_fin',
        'statut',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'montant_mensuel' => 'float',
    ];

    public function personnel()
    {
        return $this->belongsTo(personnel::class, 'id_personnel');
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
