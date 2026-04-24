<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class element_ligne_budgetaire_sortie extends Model
{
    use HasFactory;

    protected $table='element_ligne_budgetaire_sorties';
    public $timestamps=true;

    protected $fillable = [

        'libelle_elements_ligne_budgetaire_sortie',
        'code_elements_ligne_budgetaire_sortie',
        'numero_compte_elements_ligne_budgetaire_sortie',
        'description',
        'date_creation',
        'id_ligne_budgetaire_sortie',
        'id_user',
        ];
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_elements_ligne_budgetaire_sortie');
    }
    public function ligne_budgetaire_sortie(){
        return $this->belongsTo(ligne_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }

    public function donnee_ligne_budgetaire_sorties ()
    {
        return $this->hasMany(donnee_ligne_budgetaire_sortie::class,'id_element_ligne_budgetaire_sortie');

    }
}
