<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class annee_academique extends Model
{
    use HasFactory;

    protected $table='annee_academiques';
    public $timestamps=true;
    protected  $fillable = [
        'nom','description','id_user'
    ];
    public function users()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'id_user');
    }
    public function facture_etudiants()
{
    return $this->hasMany(facture_etudiant::class,'id_annee_academique');

}
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_annee_academique');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_annee_academique');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }




}
