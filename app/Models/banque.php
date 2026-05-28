<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class banque extends Model
{
    use HasFactory;
    protected $table='banques';
    public $timestamps=true;
    protected $fillable = [
        'nom_banque',
        'telephone',
        'localisation',
        'description',
        'email',
        'code',
        'id_user',
    ];
    public function users()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'id_user');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_banque');
    }
    public function reglement_etudiants(){
        return $this->hasMany(reglement_etudiant::class,'id_banque');
    }

    public function affectations()
    {
        return $this->hasMany(BanqueUser::class, 'id_banque');
    }

    public function utilisateurs()
    {
        return $this->belongsToMany(User::class, 'banque_user', 'id_banque', 'id_user')
            ->withPivot(['peut_encaisser', 'peut_decaisser', 'actif', 'date_debut', 'date_fin', 'observation'])
            ->withTimestamps();
    }

    public function transfertsSortants()
    {
        return $this->hasMany(Transfert_caisse::class, 'id_banque_depart', 'id');
    }

    public function transfertsEntrants()
    {
        return $this->hasMany(Transfert_caisse::class, 'id_banque_arrivee', 'id');
    }
}
