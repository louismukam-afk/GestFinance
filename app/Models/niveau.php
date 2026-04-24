<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class niveau extends Model
{
    use HasFactory;
    protected $table='niveaux';
    public $timestamps=true;
    protected $fillable = [
        'nom_niveau',
        'code_niveau',
        'id_cycle',
        'id_user'
    ];

    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_niveau');

    }
    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_niveau');

    }

    public function cycles()
    {
        return $this->belongsTo(cycle::class,'id_cycle');

    }
    public function user()
    {
        return $this->belongsTo(User::class,'id_user');

    }
    public function scolarite(){
        return $this->hasMany(scolarite::class,'id_niveau');
    }
    public function tranche_scolarite(){
        return $this->hasMany(tranche_scolarite::class,'id_niveau');
    }
}
