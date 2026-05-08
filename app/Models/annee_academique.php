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
    public function scolarites()
    {
        return $this->hasMany(scolarite::class,'id_annee_academique');
    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_annee_academique');
    }
    public function decaissements(){
        return $this->hasMany(decaissement::class,'id_annee_academique');
    }
    public function entree_speciales()
    {
        return $this->hasMany(entree_speciale::class,'id_annee_academique');
    }
    public function entree_speciales_utilisation()
    {
        return $this->hasMany(entree_speciale::class,'id_annee_academique_utilisation');
    }
    public function entree_speciales_remboursement()
    {
        return $this->hasMany(entree_speciale::class,'id_annee_academique_remboursement');
    }
    public function reduction_factures()
    {
        return $this->hasMany(reduction_facture::class,'id_annee_academique');
    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }




}
