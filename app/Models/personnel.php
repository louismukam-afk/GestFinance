<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class personnel extends Model
{
    use HasFactory;


    use HasFactory;
    protected $table='personnels';
    public $timestamps=true;

    protected  $fillable = [
     'nom',
    'date_naissance',
    'lieu_naissance',
    'adresse',
    'sexe',
    'statut_matrimonial',
    'email',
    'telephone',
    'telephone_whatsapp',
    'diplome',
    'niveau_etude',
    'domaine_formation',
    'date_recrutement',
    'id_user',
        'nationalite'

    ];
    public function users()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'id_user');
    }

    public function role_utilisateurs(){
        return $this->hasMany(role_utilisateur::class,'id_personnel');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_personnel');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
