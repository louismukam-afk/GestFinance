<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class donnee_budgetaire_entree extends Model
{
    use HasFactory;
    protected $table='donnee_budgetaire_entrees';
    public $timestamps=true;
    protected  $fillable = [
        'donnee_ligne_budgetaire_entree',
        'code_donnee_budgetaire_entree',
        'numero_donnee_budgetaire_entree',
        'description',
        'date_creation',
        'id_ligne_budgetaire_entree',
        'id_budget',
        'montant',
        'id_user',
    ];

    public function donnee_ligne_budgetaire_entrees()
    {
        return $this->hasMany(donnee_ligne_budgetaire_entree::class,'id_donnee_budgetaire_entree');
    }
    public function ligne_budgetaire_entrees (){
        return $this->belongsTo(ligne_budgetaire_Entree::class,'id_ligne_budgetaire_entree');
    }
    public function budgets(){
        return $this->belongsTo(budget::class,'id_budget');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
