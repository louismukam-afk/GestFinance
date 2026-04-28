<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class donnee_ligne_budgetaire_sortie extends Model
{
    use HasFactory;

    protected $table='donnee_ligne_budgetaire_sorties';
    public $timestamps=true;
    protected $fillable = [
        'donnee_ligne_budgetaire_sortie',
        'code_donnee_ligne_budgetaire_sortie',
        'numero_donne_ligne_budgetaire_sortie',
         'description',
         'date_creation',
        'id_ligne_budgetaire_sortie',
        'id_budget',
        'id_element_ligne_budgetaire_sortie',
        'id_donnee_budgetaire_sortie',
        'montant',
        'id_user',
    ];
    public function ligne_budgetaire_sortie(){
        return $this->belongsTo(ligne_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');
    }
    public function budgets(){
        return $this->belongsTo(budget::class,'id_budget');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');
    }
    public function element_ligne_budgetaire_sorties1(){
        return $this->belongsTo(element_ligne_budgetaire_sortie::class,'id_element_ligne_budgetaire_sortie');
    }

    public function donnee_budgetaire_sorties(){
        return $this->belongsTo(donnee_budgetaire_sortie::class,'id_donnee_budgetaire_sortie');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_donnee_ligne_budgetaire_sortie');
    }
    public function element_ligne_budgetaire_sorties()
    {
        return $this->belongsTo(
            \App\Models\element_ligne_budgetaire_sortie::class,
            'id_element_ligne_budgetaire_sortie'
        );
    }


}
