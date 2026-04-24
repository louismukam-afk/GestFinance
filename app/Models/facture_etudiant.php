<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facture_etudiant extends Model
{
    use HasFactory;

    protected $table='facture_etudiants';
    public $timestamps=true;

    protected  $fillable = [
            'id_cycle','id_niveau','id_user','id_filiere','id_scolarite',
            'id_frais',
            'id_tranche_scolarite',
            'id_specialite',
            'id_etudiant',
            'id_budget',
           'id_ligne_budgetaire_entree',
           'id_element_ligne_budgetaire_entree',
           'id_donnee_ligne_budgetaire_entree',
            'id_donnee_budgetaire_entree',
           'montant_total_facture',
           'numero_facture',
           'date_facture',
           'id_annee_academique',
           'type_facture',
           'id_entite'
    ];
    public function cycles(){
        return $this->belongsTo(cycle::class,'id_cycle');
    }
    public function niveaux(){
        return $this->belongsTo(niveau::class,'id_niveau');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function filieres(){
        return $this->belongsTo(filiere::class,'id_filiere');
    }
    public function scolarites(){
        return $this->belongsTo(scolarite::class,'id_scolarite');
    }
    public function frais(){
        return $this->belongsTo(frais::class,'id_frais');
    }
    public function tranche_scolarites(){
        return $this->belongsTo(tranche_scolarite::class,'id_tranche_scolarite');
    }
    public function donnee_budgetaire_entree(){
        return $this->belongsTo(donnee_budgetaire_entree::class,'id_donnee_budgetaire_entree');
    }
    public function specialites(){
        return $this->belongsTo(specialite::class,'id_specialite');
    }

    public function etudiants(){
        return $this->belongsTo(Etudiant::class,'id_etudiant');
    }
    public function budget(){
        return $this->belongsTo(budget::class,'id_budget');
    }
    public function Annee_academique(){
        return $this->belongsTo(annee_academique::class,'id_annee_academique');
    }
    public function entite(){
        return $this->belongsTo(entite::class,'id_entite');
    }
    public function ligne_budgetaire_entree(){
        return $this->belongsTo(ligne_budgetaire_Entree::class,'id_ligne_budgetaire_entree');
    }
    public function element_ligne_budgetaire_entree(){
        return $this->belongsTo(element_ligne_budgetaire_entree::class,'id_element_ligne_budgetaire_entree');
    }
    public function donnee_ligne_budgetaire_entree(){
        return $this->belongsTo(donnee_ligne_budgetaire_entree::class,'id_donnee_ligne_budgetaire_entree');
    }
    public function reglement_etudiants(){
        return $this->hasMany(reglement_etudiant::class,'id_facture_etudiant');
    }

}
