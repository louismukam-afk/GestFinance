<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table='budgets';
    public $timestamps=true;
    protected $fillable = [
        'libelle_ligne_budget',
        'date_debut',
        'date_fin',
        'description',
        'date_creation',
        'montant_global',
        'code_budget',
        'id_user',
    ];
    public function donnee_ligne_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_budget');
    }
    public function donnee_budgetaire_sorties()
    {
        return $this->hasMany(donnee_budgetaire_sortie::class,'id_budget');

    }
    public function donnee_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_budget');

    }
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_budget');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_budget');

    }
    public function entree_speciales()
    {
        return $this->hasMany(entree_speciale::class,'id_budget');
    }
    public function reduction_factures()
    {
        return $this->hasMany(reduction_facture::class,'id_budget');
    }
    public function donnee_ligne_budgetaire_sorties ()
    {
        return $this->hasMany(donnee_ligne_budgetaire_sortie::class,'id_budget');

    }

    public function user ()
    {
        return $this->belongsTo(User::class,'id_user');

    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_budget');
    }
}
