<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class filiere extends Model
{
    use HasFactory;
    protected $table='filieres';
    public $timestamps=true;
    protected $fillable = [
        'nom_filiere',
        'code_filiere',
        'description',
        'id_user'
    ];
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }

    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_filiere');

    }

    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_filiere');

    }
    public function specialites(){
        return $this->hasMany(specialite::class,'id_filiere');
    }
    public function scolarite(){
        return $this->hasMany(scolarite::class,'id_filiere');
    }
    public function tranche_scolarite(){
        return $this->hasMany(tranche_scolarite::class,'id_filiere');
    }
}
