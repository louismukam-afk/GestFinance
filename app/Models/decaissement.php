<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class decaissement extends Model
{
    use HasFactory;

    protected $table='decaissements';
    public $timestamps=true;

    protected  $fillable = [
        'id_ligne_budgetaire_sortie',
        'id_elements_ligne_budgetaire_sortie',
        'id_donnee_ligne_budgetaire_sortie',
        'id_donnee_budgetaire_sortie',
        'id_caisse',
        'id_banque',
        'numero_depense',
        'motif',
        'date_depense',
        'id_budget',
        'montant',
        'id_user',
        'id_personnel',
        'id_annee_academique',
    ];
    public function annee_academiques (){
        return $this->belongsTo(annee_academique::class,'id_annee_academique');
    }
    public function budgets(){
        return $this->belongsTo(budget::class,'id_budget');
    }
    public function personnels(){
        return $this->belongsTo(personnel::class,'id_personnel');
    }
    public function banques(){
        return $this->belongsTo(banque::class,'id_banque');
    }
    public function caisses(){
        return $this->belongsTo(caisse::class,'id_caisse');
    }

    public function donnee_budgetaire_sorties(){
        return $this->belongsTo(donnee_budgetaire_sortie::class,'id_donnee_budgetaire_sortie');
    }
    public function donnee_ligne_budgetaire_sorties(){
        return $this->belongsTo(donnee_ligne_budgetaire_sortie::class,'id_donnee_ligne_budgetaire_sortie');
    }

    public function elements_ligne_budgetaire_sorties(){
        return $this->belongsTo(element_ligne_budgetaire_sortie::class,'id_elements_ligne_budgetaire_sortie');
    }

    public function ligne_budgetaire_sorties(){
        return $this->belongsTo(ligne_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
