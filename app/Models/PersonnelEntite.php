<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonnelEntite extends Model
{
    use HasFactory;

    protected $table = 'personnel_entites';

    protected $fillable = [
        'id_personnel',
        'id_entite',
        'id_annee_academique',
        'date_debut',
        'date_fin',
        'statut',
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

    public function entite()
    {
        return $this->belongsTo(entite::class, 'id_entite');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }
}
