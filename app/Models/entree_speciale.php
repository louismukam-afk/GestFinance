<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class entree_speciale extends Model
{
    use HasFactory;

    protected $table = 'entree_speciales';

    protected $fillable = [
        'type_entree',
        'code_entree',
        'libelle',
        'nom_tiers',
        'telephone_tiers',
        'adresse_tiers',
        'date_entree',
        'date_contraction_dette',
        'date_remboursement',
        'remboursement_multiple',
        'nombre_echeances',
        'montant',
        'id_caisse',
        'id_budget',
        'id_annee_academique',
        'id_annee_academique_utilisation',
        'id_annee_academique_remboursement',
        'observations',
        'statut',
        'id_user',
    ];

    protected $casts = [
        'date_entree' => 'date',
        'date_contraction_dette' => 'date',
        'date_remboursement' => 'date',
        'remboursement_multiple' => 'boolean',
        'montant' => 'float',
    ];

    public function caisse()
    {
        return $this->belongsTo(caisse::class, 'id_caisse');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'id_budget');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }

    public function annee_utilisation()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique_utilisation');
    }

    public function annee_remboursement()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique_remboursement');
    }

    public function echeances()
    {
        return $this->hasMany(entree_speciale_echeance::class, 'id_entree_speciale');
    }

    public function decaissements()
    {
        return $this->hasMany(decaissement::class, 'id_entree_speciale');
    }

    public function transfert_caisse()
    {
        return $this->hasOne(Transfert_caisse::class, 'id_entree_speciale');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function isDette(): bool
    {
        return $this->type_entree === 'dette';
    }

    public function getMontantRembourseAttribute(): float
    {
        if (!$this->isDette()) {
            return 0;
        }

        $echeances = $this->relationLoaded('echeances')
            ? $this->echeances
            : $this->echeances()->get();

        return (float) $echeances
            ->where('statut', 'payee')
            ->sum('montant_paye');
    }

    public function getMontantNetEncaisseAttribute(): float
    {
        if (!$this->isDette()) {
            return (float) $this->montant;
        }

        return max((float) $this->montant - $this->montant_rembourse, 0);
    }
}
