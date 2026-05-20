<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiometrieImport extends Model
{
    use HasFactory;

    protected $table = 'biometrie_imports';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'fichier',
        'statut',
        'type_import',
        'total_lignes',
        'total_consolidees',
        'total_non_associees',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    public function pointages()
    {
        return $this->hasMany(BiometriePointage::class, 'id_biometrie_import');
    }

    public function heures_realisees()
    {
        return $this->hasMany(HeureRealiseeEnseignant::class, 'id_biometrie_import');
    }

    public function presences_permanents()
    {
        return $this->hasMany(PresencePermanent::class, 'id_biometrie_import');
    }
}
