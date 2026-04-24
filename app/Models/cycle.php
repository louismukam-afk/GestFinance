<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class cycle extends Model
{
    use HasFactory;
    protected $table='cycles';
    public $timestamps=true;

    protected $fillable = [
        'nom_cycle',
        'code_cycle',
        'description',
        'id_user',
    ];

    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_cycle');

    }

    public function niveaux()
    {
        return $this->hasMany(niveau::class,'id_cycle');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_cycle');

    }
    public function user(){
        return $this->belongsTo(User::class,'id_user');
    }
    public function scolarite(){
        return $this->hasMany(scolarite::class,'id_cycle');
    }
    public function tranche_scolarite(){
        return $this->hasMany(tranche_scolarite::class,'id_cycle');
    }
}
