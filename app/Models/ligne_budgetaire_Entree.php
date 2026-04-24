<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ligne_budgetaire_Entree extends Model
{
    use HasFactory;

    protected $table='ligne_budgetaire_entrees';
    public $timestamps=true;
    protected $fillable = [
        'libelle_ligne_budgetaire_entree',
        'code_ligne_budgetaire_entree',
        'numero_compte_ligne_budgetaire_entree',
        'description',
        'date_creation',
        'id_user'
    ];

    public function donnee_budgetaire_entrees()
{
    return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_ligne_budgetaire_entree');

}
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_ligne_budgetaire_entree');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_ligne_budgetaire_entree');

    }

    public function element_ligne_budgetaire_entrees()
    {
        return $this->hasMany(element_ligne_budgetaire_entree::class,'id_ligne_budgetaire_entree');

    }
    public function user(){

        return $this->belongsTo(User::class,'id_user');
    }
    public function donnee_ligne_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_ligne_budgetaire_entree');
    }
}
