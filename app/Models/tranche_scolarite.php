<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tranche_scolarite extends Model
{
    use HasFactory;

    protected $table='tranche_scolarites';
    public $timestamps=true;

    protected $fillable = [
        'nom_tranche',
        'id_scolarite',
        'id_cycle',
        'id_filiere',
        'id_niveau',
        'id_specialite',
        'date_limite',
        'montant_tranche',
        'id_user',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'id_user');
    }
    public function cycles()
    {
        return $this->belongsTo(cycle::class,'id_cycle');
    }
    public function filiere()
    {
        return $this->belongsTo(filiere::class,'id_filiere');
    }
    public function niveaux()
    {
        return $this->belongsTo(niveau::class,'id_niveau');
    }
    public function specialites()
    {
        return $this->belongsTo(specialite::class,'id_specialite');
    }
    public function facture_etudiants()
    {
        return $this->hasMany(facture_etudiant::class,'id_tranche_scolarite');

    }

    public function tranche_scolarite(){
        return $this->hasMany(scolarite::class,'id_scolarite');
    }

    public function reglement_etudiants()
    {
        return $this->hasMany(reglement_etudiant::class,'id_tranche_scolarite');

    }
}
