<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class specialite extends Model
{
    use HasFactory;

    protected $table='specialites';
    public $timestamps=true;

    protected  $fillable = [
        'nom_specialite','code_specialite','id_user','id_filiere','capacite'
    ];

    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_specialite');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_specialite');

    }
    public function filiere(){
       return $this->belongsTo(filiere::class,'id_filiere');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function scolarite(){
        return $this->hasMany(scolarite::class,'id_specialite');
    }
    public function tranche_scolarite(){
        return $this->hasMany(tranche_scolarite::class,'id_specialite');
    }
}
