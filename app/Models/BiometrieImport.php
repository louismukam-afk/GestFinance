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
        'id_import_parent',
        'mois_requete',
        'motif_requete',
        'total_lignes',
        'total_consolidees',
        'total_non_associees',
        'observations',
        'id_user',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'mois_requete' => 'date',
    ];

    public function pointages()
    {
        return $this->hasMany(BiometriePointage::class, 'id_biometrie_import');
    }

    public function scopePourEnseignants($query)
    {
        return $query->where(fn($q) => $q->whereNull('type_import')->orWhereIn('type_import', ['cours', 'manuel', 'requete']));
    }

    public function estRequete(): bool
    {
        return $this->type_import === 'requete';
    }

    public function concerneEnseignants(): bool
    {
        return $this->type_import === null || in_array($this->type_import, ['cours', 'manuel', 'requete'], true);
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
