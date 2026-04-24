<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class element_ligne_budgetaire_entree extends Model
{
    use HasFactory;

    protected $table='element_ligne_budgetaire_entrees';
    public $timestamps=true;
    protected $fillable = [
        'libelle_elements_ligne_budgetaire_entree',
        'code_elements_ligne_budgetaire_entree',
        'numero_compte_elements_ligne_budgetaire_entree',
        'description',
        'date_creation',
        'id_ligne_budgetaire_entree',
        'id_user',
    ];
    public function donnee_ligne_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_element_ligne_budgetaire_entree');
    }
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_element_ligne_budgetaire_entree');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_element_ligne_budgetaire_entree');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');
    }
    public function ligne_budgetaire_entrees(){
        return $this->belongsTo(ligne_budgetaire_Entree::class,'id_ligne_budgetaire_entree');
    }
}
