<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FactureRattrapageLigne extends Model
{
    use HasFactory;

    protected $table = 'facture_rattrapage_lignes';

    protected $fillable = [
        'id_facture_etudiant',
        'id_matiere',
        'id_programme_specialite',
        'prix_unitaire',
        'quantite',
        'montant',
        'observation',
        'id_user',
    ];

    public function facture()
    {
        return $this->belongsTo(facture_etudiant::class, 'id_facture_etudiant');
    }

    public function matiere()
    {
        return $this->belongsTo(Matiere::class, 'id_matiere');
    }

    public function programme()
    {
        return $this->belongsTo(ProgrammeSpecialite::class, 'id_programme_specialite');
    }
}
