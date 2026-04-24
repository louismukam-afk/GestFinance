<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $table='etudiants';
    public $timestamps=true;

    protected $fillable = [

        'nom',
    'telephone_whatsapp',
    'date_naissance',
     'lieu_naissance',
     'sexe',
     'email',
     'adresse',
     'departement_origine',
      'region_origine',
     'nom_pere',
    'telephone_whatsapp_pere',
    'nom_mere',
     'nom_tuteur',
     'telephone_tuteur',
     'matricule',
    'telephone_2_etudiants',
    'adresse_tuteur',
    'photo',
     'dernier_etablissement_frequente',

    ];


    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_etudiant');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_etudiant');

    }
}
