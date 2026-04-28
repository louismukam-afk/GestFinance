<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class retour_caisse extends Model
{
    use HasFactory;

    protected $table = 'retour_caisses';

    protected $fillable = [
        'id_bon_commande',
        'id_caisse',
        'id_budget',
        'id_decaissement',
        'id_ligne_budgetaire_sortie',
        'id_elements_ligne_budgetaire_sortie',
        'id_donnee_ligne_budgetaire_sortie',
        'id_donnee_budgetaire_sortie',
        'id_annee_academique',
        'id_user',
        'numero_retour',
        'motif',
        'date_retour',
        'montant',
    ];

    public function bon()
    {
        return $this->belongsTo(bon_commandeok::class, 'id_bon_commande');
    }

    public function caisse()
    {
        return $this->belongsTo(caisse::class, 'id_caisse');
    }
public function decaissement()
    {
        return $this->belongsTo(decaissement::class, 'id_decaissement');
    }
    public function budget()
    {
        return $this->belongsTo(budget::class, 'id_budget');
    }

    public function ligne_budgetaire_sortie()
    {
        return $this->belongsTo(ligne_budgetaire_sortie::class, 'id_ligne_budgetaire_sortie');
    }

    public function element_ligne_budgetaire_sortie()
    {
        return $this->belongsTo(element_ligne_budgetaire_sortie::class, 'id_elements_ligne_budgetaire_sortie');
    }

    public function donnee_ligne_budgetaire_sortie()
    {
        return $this->belongsTo(donnee_ligne_budgetaire_sortie::class, 'id_donnee_ligne_budgetaire_sortie');
    }

    public function donnee_budgetaire_sortie()
    {
        return $this->belongsTo(donnee_budgetaire_sortie::class, 'id_donnee_budgetaire_sortie');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
