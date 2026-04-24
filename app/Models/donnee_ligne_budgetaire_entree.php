<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class donnee_ligne_budgetaire_entree extends Model
{
    use HasFactory;

    protected $table='donnee_ligne_budgetaire_entrees';
    public $timestamps=true;
    protected  $fillable = [
        'donnee_ligne_budgetaire_entree',
        'code_donnee_ligne_budgetaire_entree',
        'numero_donne_ligne_budgetaire_entree',
        'description',
        'date_creation',
        'id_ligne_budgetaire_entree',
        'id_budget',
        'id_element_ligne_budgetaire_entree',
        'id_donnee_budgetaire_entree',
        'id_user',
        'montant',

    ];
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function donnee_budgetaire_entrees(){
        return $this->belongsTo(donnee_budgetaire_entree::class,'id_donnee_budgetaire_entree');
    }
    public function element_ligne_budgetaire_entrees(){
        return $this->belongsTo(element_ligne_budgetaire_entree::class,'id_element_ligne_budgetaire_entree');
    }

    public function ligne_budgetaire_entrees(){
        return $this->belongsTo(ligne_budgetaire_Entree::class,'id_ligne_budgetaire_entree');
    }

    public function budget(){
        return $this->belongsTo(budget::class,'id_budget');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_donnee_ligne_budgetaire_sortie');
    }
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_donnee_ligne_budgetaire_entree');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_donnee_ligne_budgetaire_entree');

    }
}
