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
    'numero_cnps',
    'numero_contribuable',
    'diplome',
    'niveau_etude',
    'domaine_formation',
    'date_recrutement',
        'id_user',
        'nationalite',
        'type_personnel',
        'mode_horaire',
        'categorie_horaire',
        'horaire_travail',

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
    public function cours_enseignants(){
        return $this->hasMany(CoursEnseignant::class,'id_personnel');
    }
    public function salaires_permanents(){
        return $this->hasMany(SalairePermanent::class,'id_personnel');
    }

    public function sanction_salaires(){
        return $this->hasMany(SanctionSalaire::class,'id_personnel');
    }
    public function config_rubriques_paie(){
        return $this->hasMany(ConfigRubriquePersonnel::class,'id_personnel');
    }
    public function bulletins_paie(){
        return $this->hasMany(BulletinPaie::class,'id_personnel');
    }
    public function acomptes_salaire(){
        return $this->hasMany(AcompteSalaire::class,'id_personnel');
    }
    public function disciplines(){
        return $this->hasMany(DisciplinePersonnel::class,'id_personnel');
    }
    public function personnel_entites(){
        return $this->hasMany(PersonnelEntite::class,'id_personnel');
    }
    public function emploi_permanents(){
        return $this->hasMany(EmploiPermanent::class,'id_personnel');
    }
    public function presences_permanents(){
        return $this->hasMany(PresencePermanent::class,'id_personnel');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
}
