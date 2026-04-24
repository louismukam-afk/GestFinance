<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ligne_budgetaire_sortie extends Model
{
    use HasFactory;
    protected $table='ligne_budgetaire_sorties';
    public $timestamps=true;
    protected $fillable = [
        'libelle_ligne_budgetaire_sortie',
        'code_ligne_budgetaire_sortie',
        'numero_compte_ligne_budgetaire_sortie',
        'description',
        'date_creation',
        'id_user',
    ];

    public function donnee_budgetaire_sorties()
    {
        return $this->hasMany(donnee_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');

    }

    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_ligne_budgetaire_sortie');
    }
    public function user(){

        return $this->belongsTo(User::class,'id_user');
    }
    public function element_ligne_budgetaire_sorties()
    {
        return $this->hasMany(element_ligne_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');

    }

    public function donnee_ligne_budgetaire_sorties ()
    {
        return $this->hasMany(donnee_ligne_budgetaire_sortie::class,'id_ligne_budgetaire_sortie');

    }
}
