<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class reduction_facture extends Model
{
    use HasFactory;

    protected $table = 'reduction_factures';

    protected $fillable = [
        'id_facture_etudiant',
        'id_etudiant',
        'id_entite',
        'id_specialite',
        'id_annee_academique',
        'id_budget',
        'montant',
        'motif',
        'date_reduction',
        'observations',
        'id_user',
    ];

    public function facture()
    {
        return $this->belongsTo(facture_etudiant::class, 'id_facture_etudiant');
    }

    public function etudiant()
    {
        return $this->belongsTo(Etudiant::class, 'id_etudiant');
    }

    public function entite()
    {
        return $this->belongsTo(entite::class, 'id_entite');
    }

    public function specialite()
    {
        return $this->belongsTo(specialite::class, 'id_specialite');
    }

    public function annee_academique()
    {
        return $this->belongsTo(annee_academique::class, 'id_annee_academique');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'id_budget');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }
}
