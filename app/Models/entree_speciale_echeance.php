<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class entree_speciale_echeance extends Model
{
    use HasFactory;

    protected $table = 'entree_speciale_echeances';

    protected $fillable = [
        'id_entree_speciale',
        'nom_echeance',
        'date_echeance',
        'montant',
        'statut',
        'date_paiement',
        'montant_paye',
        'id_caisse_paiement',
        'id_annee_academique_paiement',
        'id_user_paiement',
        'observations',
    ];

    protected $casts = [
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'montant' => 'float',
        'montant_paye' => 'float',
    ];

    public function entree_speciale()
    {
        return $this->belongsTo(entree_speciale::class, 'id_entree_speciale');
    }

    public function annee_paiement()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique_paiement');
    }

    public function caisse_paiement()
    {
        return $this->belongsTo(caisse::class, 'id_caisse_paiement');
    }

    public function user_paiement()
    {
        return $this->belongsTo(User::class, 'id_user_paiement');
    }
}
